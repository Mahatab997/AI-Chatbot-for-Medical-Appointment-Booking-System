<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$userMessage = strtolower($input['message'] ?? '');

$apiKey = "YOUR_OPENAI_API_KEY"; // 🔑 Put your OpenAI API key

$systemPrompt = "
You are a smart medical assistant.

If patient mentions:

Chest pain → Suggest Cardiologist
Fever → Suggest General Physician
Skin issue → Suggest Dermatologist
Eye issue → Suggest Ophthalmologist
Mental stress → Suggest Psychiatrist

Always respond like:

Advice + Ask:
'Do you want to book a [Specialist]?'
";

$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $userMessage]
    ]
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;