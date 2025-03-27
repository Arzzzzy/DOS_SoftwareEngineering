<?php   // Handle attendance data to insert in the database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);
    $response = [];

    if ($attendanceData) {
        try {
            foreach ($attendanceData as $data) {
                $studentID = $data['studentID'];
                $attendanceStatus = $data['attendanceStatus'];
                $course = $data['course'];
                $unit = $data['unit'];
                $date = date("Y-m-d");

                // Check if the student has already been marked present for the day
                $checkSql = "SELECT COUNT(*) FROM tblattendance WHERE studentRegistrationNumber = :studentID AND dateMarked = :date";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([
                    ':studentID' => $studentID,
                    ':date' => $date
                ]);
                $count = $checkStmt->fetchColumn();

                if ($count == 0) {
                    // Insert attendance if not already recorded for the day
                    $sql = "INSERT INTO tblattendance (studentRegistrationNumber, course, unit, attendanceStatus, dateMarked)  
                            VALUES (:studentID, :course, :unit, :attendanceStatus, :date)";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':studentID' => $studentID,
                        ':course' => $course,
                        ':unit' => $unit,
                        ':attendanceStatus' => $attendanceStatus,
                        ':date' => $date
                    ]);

                    $response['status'] = 'success';
                    $response['message'][] = "Attendance recorded successfully for student ID: $studentID.";
                } else {
                    $response['status'] = 'warning';
                    $response['message'][] = "Attendance already recorded for student ID: $studentID.";
                }
            }
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = "Error inserting attendance data: " . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "No attendance data received.";
    }

    echo json_encode($response);
}
?>
