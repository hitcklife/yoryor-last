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
        Schema::create("enhanced_user_reports", function (Blueprint $table) {
            $table->id();
            $table->foreignId("reporter_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("reported_user_id")->constrained("users")->onDelete("cascade");
            $table->enum("report_type", ["inappropriate_behavior", "fake_profile", "harassment", "spam", "other"]);
            $table->text("description");
            $table->jsonb("evidence")->nullable();
            $table->enum("status", ["pending", "under_review", "resolved", "dismissed"])->default("pending");
            $table->foreignId("reviewed_by")->nullable()->constrained("users");
            $table->text("admin_notes")->nullable();
            $table->timestamp("resolved_at")->nullable();
            $table->timestamps();
            
            $table->index(["reported_user_id", "status"]);
            $table->index(["reporter_id", "created_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enhanced_user_reports');
    }
};
