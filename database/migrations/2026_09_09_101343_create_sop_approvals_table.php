<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained()->cascadeOnDelete();
            $table->string('role')->comment('penyusun | pemeriksa | pengesahan');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('signature_path')->nullable()->comment('Signature image snapshot on the private disk');
            $table->boolean('is_signed')->default(false);
            $table->timestamps();

            $table->unique(['sop_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_approvals');
    }
};
