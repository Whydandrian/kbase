<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();

            // Identitas SOP
            $table->string('nomor_sop');
            $table->string('nama_sop');
            $table->string('unit_pembuat')->nullable();
            $table->string('kementerian')->nullable();
            $table->string('institusi')->nullable();
            $table->date('tgl_pembuatan')->nullable();
            $table->date('tgl_revisi')->nullable();
            $table->date('tgl_efektif')->nullable();

            // Bagian naratif (Tujuan, Ruang Lingkup, Istilah) disimpan sebagai
            // sop_items agar dapat berisi lebih dari satu butir.

            // Dokumen final yang sudah ditandatangani & distempel pimpinan
            $table->string('final_document_path')->nullable();

            // Status siklus tanda tangan
            $table->string('status')->default('draft')->index()->comment('draft | reviewed | published');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
