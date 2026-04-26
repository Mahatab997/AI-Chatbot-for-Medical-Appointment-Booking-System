<?php 
header("Content-Type: application/json");

include "../../config/openai.php";
include "../../config/database.php";

$input = json_decode(file_get_contents("php://input"), true);
$message = trim($input['message'] ?? "");

if ($message === "") {
    echo json_encode(["error" => "Message required"]);
    exit();
}

/* =========================
   STEP 1: AI → ISSUE, TIPS, & SPECIALIZATION
========================= */
$payload = [
    "model" => "openai/gpt-4o-mini",
    "response_format" => ["type" => "json_object"],
    "messages" => [
        [
            "role" => "system",
            "content" => "You are an AI medical assistant. Analyze the symptoms and return a JSON object with strictly these keys:
{
  \"issue\": \"A short, clear statement identifying the potential medical issue (e.g., 'Possible heart condition', 'Tension headache').\",
  \"tips\": \"Actionable, practical health advice or tips based on the issue (2-3 short sentences).\",
  \"specialization\": \"The recommended doctor specialization (e.g. Cardiologist, General Physician, Dermatologist, Neurologist). Leave empty if not sure.\",
  \"urgency\": \"low\" or \"medium\" or \"high\"
}
Ensure the output is valid JSON."
        ],
        [
            "role" => "user",
            "content" => $message
        ]
    ]
];

$ch = curl_init(OPENROUTER_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . OPENROUTER_API_KEY,
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$ai_content = $result['choices'][0]['message']['content'] ?? "{}";
$ai_data = json_decode($ai_content, true);

$issue = $ai_data['issue'] ?? "Unspecified symptoms";
$tips = $ai_data['tips'] ?? "Please consult a healthcare professional for an accurate diagnosis.";
$specialization = $ai_data['specialization'] ?? "";
$urgency = $ai_data['urgency'] ?? "low";

/* =========================
   FALLBACK (IMPORTANT)
========================= */
if ($specialization == "" || strlen($specialization) > 30) {
    if (strpos($message, 'chest') !== false || strpos($message, 'heart') !== false) {
        $specialization = "Cardiologist";
        $urgency = "high";
        if ($issue == "Unspecified symptoms") $issue = "Potential chest/heart condition";
    }
    elseif (strpos($message, 'skin') !== false) {
        $specialization = "Dermatologist";
        if ($issue == "Unspecified symptoms") $issue = "Dermatological issue";
    }
    elseif (strpos($message, 'head') !== false || strpos($message, 'fever') !== false) {
        $specialization = "General Physician";
        if ($issue == "Unspecified symptoms") $issue = "General symptoms (e.g. headache, fever)";
    }
}

/* =========================
   STEP 2: FETCH DOCTORS
========================= */
$doctors = [];

if ($specialization != "") {
    $stmt = $conn->prepare("SELECT id, name, experience, rating, specialization FROM doctors WHERE specialization LIKE CONCAT('%', ?, '%') LIMIT 5");
    $stmt->bind_param("s", $specialization);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $doctors[] = $row;
    }
}

/* =========================
   RESPONSE
========================= */
echo json_encode([
    "issue" => $issue,
    "tips" => $tips,
    "specialization" => $specialization,
    "urgency" => $urgency,
    "doctors" => $doctors
]);