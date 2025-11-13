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
        Schema::create("matchmaker_consultations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("matchmaker_id")->constrained()->onDelete("cascade");
            $table->foreignId("client_id")->constrained("users")->onDelete("cascade");
            $table->timestamp("scheduled_at");
            $table->integer("duration_minutes")->default(60);
            $table->enum("status", ["scheduled", "completed", "cancelled", "no_show"])->default("scheduled");
            $table->text("notes")->nullable();
            $table->decimal("fee", 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(["matchmaker_id", "scheduled_at"]);
            $table->index(["client_id", "status"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_consultations');
    }
};
