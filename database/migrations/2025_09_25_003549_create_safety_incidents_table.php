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
        Schema::create("safety_incidents", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->enum("incident_type", ["harassment", "threat", "inappropriate_content", "fake_profile", "other"]);
            $table->text("description");
            $table->jsonb("location_data")->nullable();
            $table->jsonb("evidence")->nullable();
            $table->enum("severity", ["low", "medium", "high", "critical"])->default("medium");
            $table->enum("status", ["reported", "investigating", "resolved", "dismissed"])->default("reported");
            $table->foreignId("assigned_to")->nullable()->constrained("users");
            $table->text("resolution_notes")->nullable();
            $table->timestamp("resolved_at")->nullable();
            $table->timestamps();
            
            $table->index(["user_id", "status"]);
            $table->index(["incident_type", "severity"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_incidents');
    }
};
