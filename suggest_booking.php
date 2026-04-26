<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$message = strtolower($input['message']);

$department = "";

if(strpos($message,"cardio") !== false || strpos($message,"chest") !== false){
    $department = "Cardiology";
}
elseif(strpos($message,"fever") !== false){
    $department = "General Medicine";
}
elseif(strpos($message,"skin") !== false){
    $department = "Dermatology";
}
elseif(strpos($message,"eye") !== false){
    $department = "Ophthalmology";
}
elseif(strpos($message,"stress") !== false){
    $department = "Psychiatry";
}

echo json_encode(["department"=>$department]);