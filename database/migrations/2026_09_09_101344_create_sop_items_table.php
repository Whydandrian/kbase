<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index()->comment('dasar_hukum | kualifikasi | keterkaitan | peralatan | peringatan | pencatatan | istilah');
            $table->text('content');
            $table->string('url')->nullable();
            $table->string('file_path')->nullable()->comment('Optional supporting document on the private disk');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['sop_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_items');
    }
};
