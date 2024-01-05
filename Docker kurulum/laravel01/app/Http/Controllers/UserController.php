<?php

namespace App\Http\Controllers;

use App\Events\AbonelikStarted;
use App\Models\Product;
use App\Models\ProductDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use App\Events\AbonelikRenewed;
use App\Events\AbonelikCanceled;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;




class UserController extends Controller {


    public function upload(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "uid" => "required",
            "app_id" => "required",
            "language" => "required",
            "os" => [
                "required",
                Rule::in(['ios', 'google-api'])
            ]
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 422,
                "message" => "os değeri 'ios' ya da 'google-api' olmalıdır.",
            ];
            return response()->json($data, 422);
        } else {
            $existingProduct = Product::where('uid', $request->uid)->first();
            if ($existingProduct) {
                $data = [
                    "status" => "200",
                    "message" => "OK",
                    "client-token" => $existingProduct->client_token
                ];
                return response()->json($data, 200);
            } else {
                $product = new Product();

                $product->uid = $request->uid;
                $product->app_id = $request->app_id;
                $product->language = $request->language;
                $product->os = $request->os;

                $product->save();

                $clientToken = Uuid::uuid4()->toString();
                $product->client_token = $clientToken;
                $product->save();

                // AbonelikStarted olayını tetikle

                $data = [
                    "status" => "200",
                    "message" => "Yeni cihaz eklendi",
                    "client-token" => $clientToken,
                    "product" => $product
                ];

                AbonelikStarted::dispatch($request->app_id, $request->device_id,"AbonelikStarted");

                return response()->json($data, 200);
            }
        }
    }

    public function purchase(Request $request)
    {
        $clientToken = $request->input('client_token');
        $randomHash = $request->input('hash');
        $app = $request->input('app');


        $allowedApps = ["yikama", "temizlik", "bakim", "onarim"];

        if (!in_array($app, $allowedApps)) {
            $data = [
                "status" => 400,
                "message" => "Satın almak istediğiniz app değeri 'yikama', 'temizlik', 'bakim', 'onarim' olmalıdır."
            ];

            return response()->json($data, 400);
        }

        $existingDeviceWithToken = Product::where('client_token', $clientToken)->first();

        if ($existingDeviceWithToken) {
            $existingDeviceWithApp = ProductDevice::where('client_token', $clientToken)
                ->where('app', $app)
                ->first();

            if (!$existingDeviceWithApp) {
                $lastDigit = (int)substr($randomHash, -1);
                $status = $lastDigit % 2 !== 0 ? true : false;
                $expireDate = now()->addDays(30);

                if ($status){
                    ProductDevice::create([
                        'client_token' => $clientToken,
                        'hash' => $randomHash,
                        'expire_date' => $expireDate,
                        'status' => $status,
                        'app' => $app
                    ]);

                    $data = [
                        "status" => 200,
                        "message" => "Abonelik satın alındı."
                    ];
                }else{
                    $data = [
                        "status" => 404,
                        "message" => "Hash yanlış."
                    ];
                    return response()->json($data, 404);
                }
                return response()->json($data, 200);
            } else {
                $data = [
                    "status" => 200,
                    "message" => "Abonelik mevcut."
                ];

                return response()->json($data, 200);
            }
        } else {
            $data = [
                "status" => 404,
                "message" => "Client-token bulunamadı."
            ];

            return response()->json($data, 404);
        }
    }

    public function check(Request $request)
    {
        $clientID = $request->input('client_token');

        $device = ProductDevice::where('client_token', $clientID)->first();

        if ($device) {
            $expireDate = $device->expire_date;

            // Bugünün tarihini al
            $today = now()->format('Y-m-d');

            if ($today <= $expireDate) {
                $message = "Abonelik devam etmektedir.";
            } else {
                $message = "Abonelik bitmiştir.";
            }

            $data = [
                "status" => "200",
                "expire_date" => $expireDate,
                "message" => $message
            ];
        } else {
            $data = [
                "status" => 404,
                "message" => "Client ID ile eşleşen kayıt bulunamadı."
            ];
        }

        return response()->json($data);
    }


















    public function abonelikEvent(Request $request)
    {
        // Abonelik eventini almak için gelen istekten bilgileri çıkarma
        $appID = $request->input('appID');
        $deviceID = $request->input('deviceID');
        $eventType = $request->input('eventType'); // Bu isteği hangi event tetiklediyse o bilgisini alabilirsiniz.

        // Eventler oluşturma
        if ($eventType === 'started') {
            $event = new AbonelikStarted($appID, $deviceID);
        } elseif ($eventType === 'renewed') {
            $event = new AbonelikRenewed($appID, $deviceID);
        } elseif ($eventType === 'canceled') {
            $event = new AbonelikCanceled($appID, $deviceID);
        } else {
            // Geçersiz event tipi durumunda işlem yapılabilir
            return response()->json(['message' => 'Geçersiz event tipi'], 400);
        }

        // 3rd-party endpoint'e HTTP POST isteği gönderme
        $response = Http::post('third-party-endpoint-url', [
            'appID' => $appID,
            'deviceID' => $deviceID,
            'event' => $eventType
        ]);

        // HTTP isteği başarılı ise
        if ($response->successful()) {
            return response()->json(['message' => 'Başarılı'], 200);
        } else {
            // HTTP isteği başarısız ise, tekrar deneme mekanizması burada eklenebilir
            return response()->json(['message' => 'Başarısız, tekrar denenecek'], 500);
        }

    }

    public function getinfo() {
        // Toplam Araç: device tablosundaki toplam uid sayısı
        $totalDevices = DB::table('device')->count();

        // Toplam İos: device tablosundaki os sütunundaki toplam "ios" değerleri
        $totalIOSDevices = DB::table('device')->where('os', 'ios')->count();

        // Toplam google-api: device tablosundaki os sütunundaki toplam "google-api" değerleri
        $totalGoogleAPIDevices = DB::table('device')->where('os', 'google-api')->count();

        // Toplam Alınan app: device_p tablosundaki app sütunundaki toplam değerlerin sayısı
        $totalApps = DB::table('device_p')->distinct()->count('app');

        // Toplam aktif app: device_p tablosundaki status sütunundaki değeri 1 olan toplam adet sayısı
        $totalActiveApps = DB::table('device_p')->where('status', 1)->count();

        // Geri dönüş
        return [
            'Toplam Araç' => $totalDevices,
            'Toplam İos' => $totalIOSDevices,
            'Toplam google-api' => $totalGoogleAPIDevices,
            'Toplam Alınan app' => $totalApps,
            'Toplam aktif app' => $totalActiveApps,
        ];
    }

}
