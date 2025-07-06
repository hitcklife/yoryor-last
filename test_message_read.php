<?php

require __DIR__ . '/vendor/autoload.php';

// Try to load the MessageRead class
try {
    $messageRead = new \App\Models\MessageRead();
    echo "Success: MessageRead class loaded successfully!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
