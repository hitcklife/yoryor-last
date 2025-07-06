<?php

/**
 * Test script for message edit and delete endpoints
 * 
 * Usage: php test_message_edit_delete.php
 * 
 * Make sure to:
 * 1. Update the API_BASE_URL to your actual API URL
 * 2. Update the test credentials with valid user credentials
 * 3. Ensure you have a chat with messages to test with
 */

// Configuration
$API_BASE_URL = 'http://localhost:8000/api/v1';
$TEST_EMAIL = 'test@example.com';
$TEST_PASSWORD = 'password123';

// Test credentials - update these with valid credentials
$credentials = [
    'email' => $TEST_EMAIL,
    'password' => $TEST_PASSWORD
];

echo "=== Message Edit and Delete API Test ===\n\n";

// Step 1: Authenticate
echo "1. Authenticating...\n";
$token = authenticate($credentials);
if (!$token) {
    echo "❌ Authentication failed. Please check your credentials.\n";
    exit(1);
}
echo "✅ Authentication successful\n\n";

// Step 2: Get chats
echo "2. Getting chats...\n";
$chats = getChats($token);
if (empty($chats)) {
    echo "❌ No chats found. Please create a chat first.\n";
    exit(1);
}
$chat = $chats[0];
echo "✅ Found chat ID: {$chat['id']}\n\n";

// Step 3: Get messages from the chat
echo "3. Getting messages from chat...\n";
$messages = getChatMessages($token, $chat['id']);
if (empty($messages)) {
    echo "❌ No messages found in chat. Please send a message first.\n";
    exit(1);
}
$message = $messages[0];
echo "✅ Found message ID: {$message['id']} with content: '{$message['content']}'\n\n";

// Step 4: Test message edit (only for text messages)
if ($message['message_type'] === 'text') {
    echo "4. Testing message edit...\n";
    $newContent = "Edited message content - " . date('Y-m-d H:i:s');
    $editResult = editMessage($token, $chat['id'], $message['id'], $newContent);
    if ($editResult) {
        echo "✅ Message edited successfully\n";
        echo "   New content: '{$newContent}'\n";
        echo "   Is edited: " . ($editResult['is_edited'] ? 'Yes' : 'No') . "\n";
        echo "   Edited at: {$editResult['edited_at']}\n\n";
    } else {
        echo "❌ Message edit failed\n\n";
    }
} else {
    echo "4. Skipping message edit (not a text message)\n\n";
}

// Step 5: Test message delete
echo "5. Testing message delete...\n";
$deleteResult = deleteMessage($token, $chat['id'], $message['id']);
if ($deleteResult) {
    echo "✅ Message deleted successfully\n\n";
} else {
    echo "❌ Message delete failed\n\n";
}

echo "=== Test completed ===\n";

// Helper functions
function authenticate($credentials) {
    global $API_BASE_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_BASE_URL . '/auth/authenticate');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($credentials));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data']['token'] ?? null;
    }
    
    return null;
}

function getChats($token) {
    global $API_BASE_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_BASE_URL . '/chats');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data']['chats'] ?? [];
    }
    
    return [];
}

function getChatMessages($token, $chatId) {
    global $API_BASE_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_BASE_URL . "/chats/{$chatId}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data']['messages']['data'] ?? [];
    }
    
    return [];
}

function editMessage($token, $chatId, $messageId, $newContent) {
    global $API_BASE_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_BASE_URL . "/chats/{$chatId}/messages/{$messageId}");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['content' => $newContent]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data']['message'] ?? null;
    }
    
    echo "Edit failed with HTTP code: {$httpCode}\n";
    echo "Response: {$response}\n";
    return null;
}

function deleteMessage($token, $chatId, $messageId) {
    global $API_BASE_URL;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_BASE_URL . "/chats/{$chatId}/messages/{$messageId}");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return true;
    }
    
    echo "Delete failed with HTTP code: {$httpCode}\n";
    echo "Response: {$response}\n";
    return false;
} 