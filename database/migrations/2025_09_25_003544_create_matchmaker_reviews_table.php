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
        Schema::create("matchmaker_reviews", function (Blueprint $table) {
            $table->id();
            $table->foreignId("matchmaker_id")->constrained()->onDelete("cascade");
            $table->foreignId("client_id")->constrained("users")->onDelete("cascade");
            $table->integer("rating")->unsigned();
            $table->text("review_text")->nullable();
            $table->boolean("is_anonymous")->default(false);
            $table->timestamps();
            
            $table->unique(["matchmaker_id", "client_id"]);
            $table->index(["matchmaker_id", "rating"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_reviews');
    }
};
