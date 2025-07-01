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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('device_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_name')->nullable();
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->enum('device_type', ['PHONE', 'TABLET', 'DESKTOP', 'OTHER'])->default('PHONE');
            $table->boolean('is_device')->default(true);
            $table->string('manufacturer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
