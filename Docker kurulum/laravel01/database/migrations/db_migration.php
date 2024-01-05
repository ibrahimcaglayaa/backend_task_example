<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->string("app_id");
            $table->string("language", 64);
            $table->string("client_token", 64);
            $table->string("os");
            $table->timestamps();
        });

        Schema::create('device_p', function (Blueprint $table) {
            $table->id();
            $table->string("client_token");
            $table->string("hash");
            $table->text("status", 64);
            $table->date("expire_date");
            $table->index('expire_date', 'idx_expire_date');
        });

        Schema::create('enpoint', function (Blueprint $table) {
            $table->id();
            $table->string("url");
            $table->string("log");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device');
        Schema::dropIfExists('device_p');
        Schema::dropIfExists('enpoint');
    }
};
