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
        Schema::create("matchmaker_availability", function (Blueprint $table) {
            $table->id();
            $table->foreignId("matchmaker_id")->constrained()->onDelete("cascade");
            $table->date("date");
            $table->time("start_time");
            $table->time("end_time");
            $table->enum("status", ["available", "busy", "unavailable"])->default("available");
            $table->text("notes")->nullable();
            $table->timestamps();
            
            $table->index(["matchmaker_id", "date"]);
            $table->index(["date", "status"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_availability');
    }
};
