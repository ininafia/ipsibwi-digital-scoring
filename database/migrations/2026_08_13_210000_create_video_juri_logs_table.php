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
        if (!Schema::hasTable('video_juri_logs')) {
            Schema::create('video_juri_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pertandingan')->nullable();
                $table->string('posisi_juri', 50)->nullable();
                $table->unsignedBigInteger('id_petugas')->nullable();
                $table->string('nama_juri', 150)->nullable();
                $table->string('filename', 255);
                $table->string('file_path', 255);
                $table->integer('duration_seconds')->default(0);
                $table->bigInteger('file_size')->default(0);
                $table->timestamps();

                $table->index('id_pertandingan');
                $table->index('posisi_juri');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_juri_logs');
    }
};
