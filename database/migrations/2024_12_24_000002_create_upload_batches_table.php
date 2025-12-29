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
        Schema::create('upload_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('filename');
            $table->string('periode')->nullable();
            $table->integer('total_records');
            $table->string('uploaded_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Store additional info like BJR, etc.
            $table->timestamps();
            
            $table->index('periode');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_batches');
    }
};

