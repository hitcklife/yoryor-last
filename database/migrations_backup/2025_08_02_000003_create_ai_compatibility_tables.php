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
        // Store compatibility scores between users
        Schema::create('user_compatibility_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id_1');
            $table->unsignedBigInteger('user_id_2');
            $table->decimal('overall_score', 5, 2);
            $table->decimal('basic_score', 5, 2)->nullable();
            $table->decimal('cultural_score', 5, 2)->nullable();
            $table->decimal('lifestyle_score', 5, 2)->nullable();
            $table->decimal('interest_score', 5, 2)->nullable();
            $table->decimal('ai_score', 5, 2)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->unique(['user_id_1', 'user_id_2']);
            $table->index(['user_id_1', 'overall_score']);
            $table->index(['user_id_2', 'overall_score']);
            
            $table->foreign('user_id_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_2')->references('id')->on('users')->onDelete('cascade');
        });

        // Add AI-related columns to profiles
        Schema::table('profiles', function (Blueprint $table) {
            $table->jsonb('ai_personality_traits')->nullable();
            $table->jsonb('ai_extracted_interests')->nullable();
            $table->jsonb('ai_values')->nullable();
            $table->jsonb('ai_relationship_goals')->nullable();
            $table->timestamp('ai_analyzed_at')->nullable();
        });

        // Store AI conversation suggestions
        Schema::create('ai_conversation_starters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->jsonb('starters');
            $table->integer('times_used')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'target_user_id']);
        });

        // Cultural compatibility scores
        Schema::create('cultural_compatibility_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id_1');
            $table->unsignedBigInteger('user_id_2');
            $table->decimal('cultural_score', 5, 2);
            $table->text('religious_compatibility')->nullable();
            $table->text('family_values_alignment')->nullable();
            $table->text('lifestyle_compatibility')->nullable();
            $table->jsonb('recommendations')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id_1', 'user_id_2']);
            $table->foreign('user_id_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_2')->references('id')->on('users')->onDelete('cascade');
        });

        // AI usage tracking for billing
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('operation');
            $table->integer('tokens_used');
            $table->decimal('estimated_cost', 8, 4);
            $table->string('model');
            $table->boolean('success')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['operation', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('cultural_compatibility_analyses');
        Schema::dropIfExists('ai_conversation_starters');
        Schema::dropIfExists('user_compatibility_scores');
        
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ai_personality_traits',
                'ai_extracted_interests',
                'ai_values',
                'ai_relationship_goals',
                'ai_analyzed_at'
            ]);
        });
    }
};