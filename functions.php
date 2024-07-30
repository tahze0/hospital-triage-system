<?php

require 'db.php';

function addPatient($name, $severity) {
    $db = getDbConnection();
    if (!$db) {
        error_log("Database connection failed in addPatient");
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // input validation
    if (empty($name) || !is_string($name) || strlen($name) > 100) {
        error_log("Invalid name in addPatient: " . $name);
        return ['success' => false, 'message' => 'Invalid name'];
    }
    if (!is_numeric($severity) || $severity < 1 || $severity > 5) {
        error_log("Invalid severity in addPatient: " . $severity);
        return ['success' => false, 'message' => 'Invalid severity'];
    }

    try {
        $code = generateUniqueCode($db);
        
        $stmt = $db->prepare("INSERT INTO patients (name, severity, code) VALUES (?, ?, ?) RETURNING id");
        $stmt->execute([$name, $severity, $code]);
        $patientId = $stmt->fetchColumn();
        
        error_log("Patient added successfully: id=$patientId, name=$name, severity=$severity, code=$code");
        return ['success' => true, 'message' => 'Patient added successfully', 'id' => $patientId, 'code' => $code];
    } catch (PDOException $e) {
        error_log("Error in addPatient: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while adding the patient: ' . $e->getMessage()];
    }
}

function updatePatientStatus($id, $status)  {
    error_log("Updating patient status: id=$id, status=$status");
    $db = getDbConnection();
    if (!$db) {
        error_log("Database connection failed in updatePatientStatus");
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $db->prepare("UPDATE patients SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);
        error_log("Status update result: " . ($result ? "success" : "failure"));
        return ['success' => $result, 'message' => $result ? 'Status updated successfully' : 'Failed to update status'];
    } catch (PDOException $e) {
        error_log("Error updating patient status: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while updating the status'];
    }
}

function calculateBaseWaitTime($severity) {
    $base_time = [
        5 => 60,  // Minimal: 10 minutes
        4 => 45,  // Low: 20 minutes
        3 => 30,  // Moderate: 30 minutes
        2 => 20,  // High: 45 minutes
        1 => 10   // Critical: 60 minutes
    ];
    
    return $base_time[$severity] ?? 60;
}

function calculateWaitTimes($patients) {
    $totalWaitTime = 0;
    foreach ($patients as &$patient) {
        if ($patient['status'] === 'Waiting') {
            $baseTime = calculateBaseWaitTime($patient['severity']);
            $patient['estimated_wait_time'] = $totalWaitTime + $baseTime;
            $totalWaitTime += $baseTime;
        } else {
            $patient['estimated_wait_time'] = null;
        }
    }
    return $patients;
}

function getPatientQueue() {
    $db = getDbConnection();
    if (!$db)  {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $db->query("SELECT * FROM patients WHERE status IN ('Waiting', 'In Treatment') ORDER BY severity DESC, arrival_time ASC");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $patients = calculateWaitTimes($patients);
        
        return ['success' => true, 'data' => $patients];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'An error occurred while fetching the patient queue'];
    }
}

function patientSignIn($name, $code) {
    $db = getDbConnection();
    if (!$db) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $stmt = $db->prepare("SELECT id, severity FROM patients WHERE name = ? AND code = ? AND status = 'Waiting'");
        $stmt->execute([$name, $code]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            // calculate estimated wait time and queue position
            $stmt = $db->prepare("SELECT COUNT(*) as queue_position, SUM(CASE 
                WHEN severity = 5 THEN 60
                WHEN severity = 4 THEN 45
                WHEN severity = 3 THEN 30
                WHEN severity = 2 THEN 20
                WHEN severity = 1 THEN 10
                ELSE 0 END) as total_wait_time
            FROM patients 
            WHERE status = 'Waiting' AND (severity > ? OR (severity = ? AND id <= ?))");
            $stmt->execute([$patient['severity'], $patient['severity'], $patient['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $estimated_wait_time = $result['total_wait_time'];
            $queue_position = $result['queue_position'];

            return [
                'success' => true,
                'estimated_wait_time' => $estimated_wait_time,
                'queue_position' => $queue_position
            ];
        } else {
            return ['success' => false, 'message' => 'Patient not found or already in treatment'];
        }
    } catch (PDOException $e) {
        error_log("Error during patient sign-in: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred during sign-in'];
    }
}

function generateUniqueCode($db) {
    do {
        $code = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $stmt = $db->prepare("SELECT 1 FROM patients WHERE code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn();
    } while ($exists);
    return $code;
}


?>