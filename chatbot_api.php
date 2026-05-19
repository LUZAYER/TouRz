<?php
error_reporting(0);
ob_start();
set_time_limit(300);
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if ($query === '') {
    echo json_encode(['error' => 'Empty query']);
    exit;
}

// ===== LOAD & OPTIMIZE KNOWLEDGE BASE (Pseudo-RAG) =====
$knowledgePath = __DIR__ . '/Chatbot/knowledge.txt';
$knowledgeFull = file_exists($knowledgePath) ? file_get_contents($knowledgePath) : 'You are the TouRz chatbot.';

$lower_query = strtolower($query);
$optimized_context = "";

// 1. Always include the prices and schedule (it's short)
$parts = explode("--- DESTINATION DETAILS ---", $knowledgeFull);
if (count($parts) > 1) {
    $optimized_context .= $parts[0] . "\n\n";
    $detailsPart = $parts[1];
} else {
    $optimized_context .= $knowledgeFull;
    $detailsPart = "";
}

// 2. Add 'About' info if queried
if (strpos($lower_query, 'about') !== false || strpos($lower_query, 'who') !== false) {
    $optimized_context .= "ABOUT TOURZ: Welcome to TouRz! We started in 2023 with a goal of providing extraordinary travel experiences around Bangladesh and beyond.\n";
}

// 3. Extract and match specific destination chunks to drastically reduce token count
$destinations = ['cox', 'saint martin', 'sajek', 'sundarban', 'bandarban', 'kuakata', 'khagrachari', 'sreemangal', 'ladakh', 'bali', 'edinburgh'];

foreach ($destinations as $dest) {
    if (strpos($lower_query, $dest) !== false) {
        // Find the block in the detailsPart
        $searchTag = "Destination: " . (ucwords($dest));
        // Use a case-insensitive robust regex or substring
        $pos = stripos($detailsPart, "Destination: ");
        if ($pos !== false) {
            // A simple regex to grab the specific destination block
            if (preg_match('/Destination:.*?' . preg_quote($dest, '/') . '.*?(?=Destination:|$)/is', $detailsPart, $matches)) {
                $optimized_context .= $matches[0] . "\n";
            }
        }
    }
}

// ===== OLLAMA AI INTEGRATION =====
$response = '';

// Setup data for Ollama
$ollama_data = [
    'model' => 'llama3.1',
    'messages' => [
        [
            'role' => 'system',
            'content' => $optimized_context . "\n\nCRITICAL INSTRUCTION: Answer briefly and conversationally using markdown. Do NOT make up information. Use ONLY the info provided above."
        ],
        [
            'role' => 'user',
            'content' => $query
        ]
    ],
    'stream' => false,
    'temperature' => 0.3
];

$ch = curl_init('http://localhost:11434/api/chat');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ollama_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// Optional timeout (Now optimized, it should take < 10 seconds, but we leave 180s just in case)
curl_setopt($ch, CURLOPT_TIMEOUT, 180);

$api_response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);

if ($api_response && $http_status === 200) {
    $resp_data = json_decode($api_response, true);
    if (isset($resp_data['message']['content'])) {
        $response = $resp_data['message']['content'];
    } else {
        $response = "⚠️ Received an unexpected format from the AI engine.";
    }
} else {
    $response = "⚠️ Could not connect to the AI engine. Error: " . ($error_msg ?? "HTTP $http_status") . ". Check if Ollama is running.";
}

// ===== LOG TO DATABASE =====
$stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, query_text, response_text) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $query, $response);
$stmt->execute();
$stmt->close();

// ===== LOG TO FILE =====
$logDir = __DIR__ . '/Chatbot/logs';
if (!is_dir($logDir))
    mkdir($logDir, 0777, true);
$logLine = date('Y-m-d H:i:s') . " | User:" . ($user_id ?? 'guest') . " | Q: " . $query . " | R: " . substr($response, 0, 100) . "...\n";
file_put_contents($logDir . '/chatbot_queries.log', $logLine, FILE_APPEND);

ob_end_clean();
$json_out = json_encode(['response' => $response], JSON_INVALID_UTF8_SUBSTITUTE);
if ($json_out === false) {
    echo json_encode(['response' => "Encoding error: " . json_last_error_msg()]);
} else {
    echo $json_out;
}
$conn->close();
?>