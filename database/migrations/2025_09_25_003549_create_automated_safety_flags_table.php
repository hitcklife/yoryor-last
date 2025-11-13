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
        Schema::create("automated_safety_flags", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("flag_type");
            $table->text("description");
            $table->jsonb("trigger_data")->nullable();
            $table->enum("status", ["active", "reviewed", "resolved", "false_positive"])->default("active");
            $table->integer("confidence_score")->default(0);
            $table->foreignId("reviewed_by")->nullable()->constrained("users");
            $table->text("review_notes")->nullable();
            $table->timestamp("reviewed_at")->nullable();
            $table->timestamps();
            
            $table->index(["user_id", "status"]);
            $table->index(["flag_type", "confidence_score"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automated_safety_flags');
    }
};
