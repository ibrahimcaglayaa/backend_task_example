

- Verilen task'deki isteneleri uygulamak için uygun post araçlarını kullanın (postman vs );

	Task da istenilen koşulların hepsi yapılmıştır:
	

	* http://[localhostunuz]/api/register

	-örnek bir post

	{
	    "uid": "1",
	    "app_id": "1",
	    "language": "tr",
	    "os": "google-api" 
	}



	* http://[localhostunuz]/api/purchase

	-örnek bir post

	{
	    "client_token": "c281b371-254e-42c9-a830-1baf2a30ff52",
	    "hash":"111",
	    "app":"temizlik"
	}


	*http://[localhostunuz]/api/info / RAPORLAMA

	-get ile gelen örnek bir veri

	{
    "Toplam Araç": 6,
    "Toplam İos": 6,
    "Toplam google-api": 0,
    "Toplam Alınan app": 2,
    "Toplam aktif app": 2
	}



	*WORKER

	Buradaki önemli noktalardan bir tanesi DB’de bekleyen kayıtların adet olarak milyonlarca (10
	milyon+) olabileceği göz önünde bulundurulup bu bekleyen kayıtların kısa sürede
	eritilebilmesi gerekiyor

	-      $table->date("expire_date");
           $table->index('expire_date', 'idx_expire_date');

