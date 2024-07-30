<?php

require 'functions.php';


header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
error_log("API called with action: $action");

try {
    switch ($action) {
        case 'add_patient':
            $name = $_POST['name'] ?? '';
            $severity = intval($_POST['severity'] ?? 0);
            error_log("Add patient request: name=$name, severity=$severity");
            if (empty($name) || $severity < 1 || $severity > 5) {
                error_log("Invalid input for add_patient");
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            } else {
                $result = addPatient($name, $severity);
                error_log("Add patient result: " . json_encode($result));
                echo json_encode($result);
            }
            break;
        case 'update_status':
            $id = intval($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            error_log("Update status request: id=$id, status=$status");
            if ($id <= 0 || !in_array($status, ['Waiting', 'In Treatment', 'Discharged'])) {
                error_log("Invalid input for update_status");
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            } else {
                $result = updatePatientStatus($id, $status);
                error_log("Update status result: " . json_encode($result));
                echo json_encode($result);
            }
            break;
        case 'get_queue':
            $result = getPatientQueue();
            error_log("Get queue result: " . json_encode($result));
            echo json_encode($result);
            break;
        case 'patient_signin':
            $name = $_POST['name'] ?? '';
            $code = $_POST['code'] ?? '';
            error_log("Patient sign-in request: name=$name, code=$code");
            if (empty($name) || empty($code)) {
                error_log("Invalid input for patient_signin");
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
            } else {
                $result = patientSignIn($name, $code);
                error_log("Patient sign-in result: " . json_encode($result));
                echo json_encode($result);
            }
            break;
        default:
            error_log("Invalid action: $action");
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Unhandled exception in api.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
}


?>
