<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('university');
            $table->string('study_program')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->string('drive_url')->nullable()->comment('External link (e.g. Google Drive)');
            $table->string('file_path')->nullable()->comment('Uploaded PDF path on the public disk');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};
