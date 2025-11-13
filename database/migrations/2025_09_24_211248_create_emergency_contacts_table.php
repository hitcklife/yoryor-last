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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Contact information
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->enum('relationship', [
                'parent',
                'sibling',
                'spouse',
                'friend',
                'other'
            ]);

            // Contact preferences
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_receive_alerts')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'is_primary']);
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
