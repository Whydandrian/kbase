<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_no');
            $table->text('kegiatan');
            $table->string('pelaksana')->nullable()->comment('Actor / unit performing the step');
            $table->boolean('is_decision')->default(false);
            $table->string('kelengkapan')->nullable();
            $table->string('waktu')->nullable();
            $table->string('output')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('image_path')->nullable()->comment('Optional flowchart symbol/diagram image on the private disk');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_flow_steps');
    }
};
