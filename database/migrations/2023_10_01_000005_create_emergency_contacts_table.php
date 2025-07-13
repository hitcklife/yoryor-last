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
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User who owns this emergency contact
            $table->string('name')->notNullable(); // Name of the emergency contact
            $table->string('phone')->notNullable(); // Phone number of the emergency contact
            $table->string('relationship')->nullable(); // Relationship to the user
            $table->boolean('is_primary')->default(false); // Whether this is the primary emergency contact
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Add indexes for better performance
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
