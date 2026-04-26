<?php
include "deepseek_config.php";

$message = $_POST['message'] ?? '';

if(empty($message)){
    echo "Please describe your symptoms.";
    exit;
}

$system_prompt = "You are an AI medical assistant.
Based on user symptoms:
1) Suggest ONLY ONE doctor type from:
Cardiologist, Dermatologist, Neurologist, General Physician.
2) Give short advice.
3) If emergency, say EMERGENCY immediately.";

$payload = json_encode([
    "model" => "deepseek-chat",
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $message]
    ],
    "temperature" => 0.4
], JSON_UNESCAPED_UNICODE);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.deepseek.com/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . trim($api_key),
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo "cURL Error: " . curl_error($ch);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if(isset($result['error'])){
    echo "DeepSeek Error: " . $result['error']['message'];
    exit;
}

if(!isset($result['choices'][0]['message']['content'])){
    echo "AI temporarily unavailable.";
    exit;
}

echo $result['choices'][0]['message']['content'];
