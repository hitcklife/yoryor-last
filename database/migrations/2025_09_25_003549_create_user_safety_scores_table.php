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
        Schema::create("user_safety_scores", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->integer("overall_score")->default(100);
            $table->integer("behavior_score")->default(100);
            $table->integer("verification_score")->default(0);
            $table->integer("report_count")->default(0);
            $table->integer("positive_interactions")->default(0);
            $table->timestamp("last_calculated_at");
            $table->timestamps();
            
            $table->unique("user_id");
            $table->index(["overall_score", "last_calculated_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_safety_scores');
    }
};
