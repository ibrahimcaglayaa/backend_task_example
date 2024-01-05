<?php

namespace App\Console;
use App\Events\AbonelikCanceled;
use App\Events\AbonelikStarted;
use App\Models\ProductDevice;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        while (true) {
            $expireDevices = ProductDevice::whereDate('expire_date', '<', Carbon::now())->get();

            foreach ($expireDevices as $device) {
                $device->status = 0;
                $device->save();
                AbonelikCanceled::dispatch($expireDevices->app_id, $expireDevices->device_id,"AbonelikCanceled");

            }

            sleep(60); // 60 saniye bekleme süresi
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
