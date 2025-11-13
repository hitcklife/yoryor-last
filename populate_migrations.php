<?php

// Script to populate individual migration files with proper content

$migrations = [
    '2025_09_25_003541_create_matchmaker_availability_table.php' => '
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
        });',

    '2025_09_25_003541_create_matchmaker_consultations_table.php' => '
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
        });',

    '2025_09_25_003544_create_matchmaker_introductions_table.php' => '
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
        });',

    '2025_09_25_003544_create_matchmaker_reviews_table.php' => '
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
        });',

    '2025_09_25_003544_create_enhanced_user_reports_table.php' => '
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
        });',

    '2025_09_25_003545_create_report_categories_table.php' => '
        Schema::create("report_categories", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->boolean("is_active")->default(true);
            $table->integer("sort_order")->default(0);
            $table->timestamps();
        });',

    '2025_09_25_003547_create_report_evidence_table.php' => '
        Schema::create("report_evidence", function (Blueprint $table) {
            $table->id();
            $table->foreignId("report_id")->constrained("enhanced_user_reports")->onDelete("cascade");
            $table->string("evidence_type");
            $table->string("file_path")->nullable();
            $table->text("description")->nullable();
            $table->timestamps();
            
            $table->index(["report_id", "evidence_type"]);
        });',

    '2025_09_25_003549_create_safety_incidents_table.php' => '
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
        });',

    '2025_09_25_003549_create_user_safety_scores_table.php' => '
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
        });',

    '2025_09_25_003549_create_automated_safety_flags_table.php' => '
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
        });',

    '2025_09_25_003552_create_plan_features_table.php' => '
        Schema::create("plan_features", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->string("category")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });',

    '2025_09_25_003552_create_subscription_features_table.php' => '
        Schema::create("subscription_features", function (Blueprint $table) {
            $table->id();
            $table->foreignId("plan_id")->constrained("subscription_plans")->onDelete("cascade");
            $table->foreignId("feature_id")->constrained("plan_features")->onDelete("cascade");
            $table->integer("limit_value")->nullable();
            $table->boolean("is_unlimited")->default(false);
            $table->timestamps();
            
            $table->unique(["plan_id", "feature_id"]);
        });',

    '2025_09_25_003553_create_permission_user_table.php' => '
        Schema::create("permission_user", function (Blueprint $table) {
            $table->id();
            $table->foreignId("permission_id")->constrained()->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->timestamps();
            
            $table->unique(["permission_id", "user_id"]);
        });',

    '2025_09_25_003554_create_pulse_values_table.php' => '
        Schema::create("pulse_values", function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("timestamp");
            $table->string("type");
            $table->mediumText("key");
            $table->uuid("key_hash")->storedAs("md5(\"key\")::uuid");
            $table->mediumText("value");

            $table->index("timestamp");
            $table->index("type");
            $table->unique(["type", "key_hash"]);
        });',

    '2025_09_25_003556_create_pulse_entries_table.php' => '
        Schema::create("pulse_entries", function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("timestamp");
            $table->string("type");
            $table->mediumText("key");
            $table->uuid("key_hash")->storedAs("md5(\"key\")::uuid");
            $table->bigInteger("value")->nullable();

            $table->index("timestamp");
            $table->index("type");
            $table->index("key_hash");
            $table->index(["timestamp", "type", "key_hash", "value"]);
        });',

    '2025_09_25_003557_create_pulse_aggregates_table.php' => '
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
        });'
];

foreach ($migrations as $filename => $schema) {
    $filepath = "/Users/khurshidjumaboev/Desktop/yoryor/yoryor-last/database/migrations/$filename";
    $content = file_get_contents($filepath);
    
    // Replace the Schema::create part
    $newContent = preg_replace(
        '/Schema::create\([^}]+}\);/s',
        trim($schema),
        $content
    );
    
    file_put_contents($filepath, $newContent);
    echo "Updated $filename\n";
}

echo "All migration files updated!\n";
