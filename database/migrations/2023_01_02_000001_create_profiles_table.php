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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->enum('gender', ['male', 'female', 'non-binary', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city', 85)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->index(['gender', 'date_of_birth']);
            $table->index('city');
            $table->index('state');
            $table->index('province');
            $table->index(['latitude', 'longitude']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
