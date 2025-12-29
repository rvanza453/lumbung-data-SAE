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
        Schema::create('karyawan_performances', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->nullable();
            $table->string('nama');
            $table->string('afd')->nullable();
            $table->decimal('hk', 8, 2); // Hari Kerja
            $table->integer('jjg'); // Total Janjang
            $table->decimal('ton', 10, 3); // Tonase
            $table->decimal('kg_per_hk', 10, 2); // Produktivitas
            $table->string('periode')->nullable(); // Format: YYYY-MM atau custom label
            $table->date('tanggal_upload')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->string('batch_id')->nullable(); // Untuk grouping per upload
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('nama');
            $table->index('afd');
            $table->index('periode');
            $table->index('batch_id');
            $table->index('tanggal_upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_performances');
    }
};

