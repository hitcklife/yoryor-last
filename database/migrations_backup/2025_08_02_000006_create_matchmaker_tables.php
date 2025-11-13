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
        // Professional matchmakers table
        Schema::create('matchmakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name')->nullable();
            $table->text('bio');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->jsonb('specializations')->nullable(); // e.g., ['traditional', 'modern', 'religious']
            $table->jsonb('languages')->nullable();
            $table->integer('years_experience')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->integer('successful_matches')->default(0);
            $table->integer('total_clients')->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['is_active', 'verification_status']);
            $table->index('rating');
        });

        // Matchmaker services/packages
        Schema::create('matchmaker_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('duration_unit', ['days', 'weeks', 'months'])->default('months');
            $table->integer('duration_value')->default(1);
            $table->integer('max_introductions')->nullable(); // Max number of introductions
            $table->jsonb('features')->nullable(); // List of features included
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['matchmaker_id', 'is_active']);
        });

        // Matchmaker client relationships
        Schema::create('matchmaker_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('matchmaker_services');
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->text('goals')->nullable(); // Client's relationship goals
            $table->jsonb('preferences')->nullable(); // Detailed preferences
            $table->date('contract_start_date');
            $table->date('contract_end_date')->nullable();
            $table->integer('introductions_made')->default(0);
            $table->integer('successful_matches')->default(0);
            $table->text('notes')->nullable(); // Matchmaker's private notes
            $table->timestamps();
            
            $table->unique(['matchmaker_id', 'client_id']);
            $table->index(['client_id', 'status']);
            $table->index('contract_end_date');
        });

        // Matchmaker introductions/suggestions
        Schema::create('matchmaker_introductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('suggested_user_id')->constrained('users')->onDelete('cascade');
            $table->text('introduction_message')->nullable();
            $table->text('compatibility_notes'); // Why this is a good match
            $table->decimal('compatibility_score', 5, 2)->nullable();
            $table->enum('client_response', ['pending', 'interested', 'not_interested', 'met'])->default('pending');
            $table->enum('suggested_user_response', ['pending', 'interested', 'not_interested', 'met'])->default('pending');
            $table->timestamp('client_responded_at')->nullable();
            $table->timestamp('suggested_user_responded_at')->nullable();
            $table->boolean('meeting_arranged')->default(false);
            $table->timestamp('meeting_date')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->enum('outcome', ['pending', 'successful', 'unsuccessful', 'ongoing'])->default('pending');
            $table->timestamps();
            
            $table->index(['matchmaker_id', 'client_id']);
            $table->index(['client_id', 'client_response']);
            $table->index(['suggested_user_id', 'suggested_user_response']);
        });

        // Matchmaker reviews
        Schema::create('matchmaker_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('review')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->boolean('is_verified_client')->default(false);
            $table->enum('service_type', ['full_service', 'consultation', 'introduction'])->nullable();
            $table->timestamps();
            
            $table->unique(['matchmaker_id', 'user_id']);
            $table->index(['matchmaker_id', 'rating']);
        });

        // Matchmaker availability/consultation slots
        Schema::create('matchmaker_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->index(['matchmaker_id', 'day_of_week']);
        });

        // Consultation bookings
        Schema::create('matchmaker_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->default(30);
            $table->enum('type', ['initial', 'follow_up', 'strategy'])->default('initial');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('meeting_link')->nullable(); // Video call link
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['matchmaker_id', 'scheduled_at']);
            $table->index(['user_id', 'status']);
        });

        // Add matchmaker-related fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_matchmaker')->default(false);
            $table->boolean('prefers_matchmaker')->default(false);
            $table->foreignId('assigned_matchmaker_id')->nullable()->constrained('matchmakers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_matchmaker_id']);
            $table->dropColumn(['is_matchmaker', 'prefers_matchmaker', 'assigned_matchmaker_id']);
        });

        Schema::dropIfExists('matchmaker_consultations');
        Schema::dropIfExists('matchmaker_availability');
        Schema::dropIfExists('matchmaker_reviews');
        Schema::dropIfExists('matchmaker_introductions');
        Schema::dropIfExists('matchmaker_clients');
        Schema::dropIfExists('matchmaker_services');
        Schema::dropIfExists('matchmakers');
    }
};