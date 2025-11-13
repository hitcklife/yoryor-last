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
        Schema::create("matchmaker_introductions", function (Blueprint $table) {
            $table->id();
            $table->foreignId("matchmaker_id")->constrained()->onDelete("cascade");
            $table->foreignId("user1_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("user2_id")->constrained("users")->onDelete("cascade");
            $table->enum("status", ["pending", "accepted", "declined", "expired"])->default("pending");
            $table->text("introduction_message")->nullable();
            $table->timestamp("introduced_at")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->timestamps();
            
            $table->index(["matchmaker_id", "status"]);
            $table->index(["user1_id", "user2_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_introductions');
    }
};
