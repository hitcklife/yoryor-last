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
        Schema::create("pulse_aggregates", function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("timestamp");
            $table->string("type");
            $table->string("key");
            $table->uuid("key_hash")->storedAs("md5(\"key\")::uuid");
            $table->string("aggregate");
            $table->bigInteger("value");

            $table->index("timestamp");
            $table->index(["type", "key_hash"]);
            $table->unique(["type", "key_hash", "aggregate"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pulse_aggregates');
    }
};
