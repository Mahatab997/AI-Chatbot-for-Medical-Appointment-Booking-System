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
   STEP 1: AI → SPECIALIZATION
========================= */
$payload = [
    "model" => "openai/gpt-4o-mini",
    "messages" => [
        [
            "role" => "system",
            "content" => "Return ONLY doctor specialization based on symptoms. Example: chest pain → Cardiologist"
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
$specialization = trim($result['choices'][0]['message']['content'] ?? "");

/* =========================
   FALLBACK (IMPORTANT)
========================= */
if ($specialization == "" || strlen($specialization) > 30) {
    if (strpos($message, 'chest') !== false) $specialization = "Cardiologist";
    elseif (strpos($message, 'skin') !== false) $specialization = "Dermatologist";
    elseif (strpos($message, 'head') !== false) $specialization = "Neurologist";
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
    "reply" => "Based on your symptoms, you should consult a $specialization.",
    "specialization" => $specialization,
    "doctors" => $doctors
]);