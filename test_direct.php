<?php
require_once "db.php";
$ollama_data = [
    "model" => "llama3.1",
    "messages" => [
        ["role" => "system", "content" => "You are a bot."],
        ["role" => "user", "content" => "Hi"]
    ],
    "stream" => false
];
$ch = curl_init("http://localhost:11434/api/chat");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ollama_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
echo "Sending request...\n";
$time_start = microtime(true);
$api_response = curl_exec($ch);
echo "Time: " . (microtime(true) - $time_start) . "\n";
echo "Response: $api_response\n";
