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
        Schema::create('gelanggang_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_path');
            $table->string('serve_host')->default('127.0.0.1');
            $table->unsignedInteger('serve_port')->default(8000);
            $table->unsignedInteger('reverb_port')->default(8080);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gelanggang_configs');
    }
};
