<?php

// Simple script to test the seeder
echo "Starting test...\n";

// Run the migration fresh to apply our changes
echo "Running migrations...\n";
passthru('php artisan migrate:fresh');

// Run the seeder
echo "Running seeders...\n";
passthru('php artisan db:seed');

echo "Test completed.\n";
