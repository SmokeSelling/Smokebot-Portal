<?php
header('Content-Type: application/json');
require 'db_connect.php'; // Your existing connection file

$input = json_decode(file_get_contents('php://input'), true);

// HWID Validation
if(isset($input['license_key']) && isset($input['hwid'])){
    $stmt = $conn->prepare("SELECT hwid FROM licenses WHERE license_key = ?");
    $stmt->bind_param("s", $input['license_key']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        // First activation
        if(empty($row['hwid'])){
            $update = $conn->prepare("UPDATE licenses SET hwid = ? WHERE license_key = ?");
            $update->bind_param("ss", $input['hwid'], $input['license_key']);
            $update->execute();
            echo json_encode(['status' => 'hwid_registered']);
        } 
        // Subsequent checks
        else {
            echo json_encode([
                'status' => $row['hwid'] === $input['hwid'] ? 'valid' : 'invalid_hwid'
            ]);
        }
    } else {
        echo json_encode(['error' => 'invalid_license']);
    }
} else {
    echo json_encode(['error' => 'missing_data']);
}
?>