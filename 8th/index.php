<?php
header('Content-Type: application/json');
// Function to get the SQLite database connection
function getDbConnection() {
    $dbFile = '../results.db'; // Path to your SQLite database file
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Function to get results for the 1st semester
function get3rdSemesterResults() {
    $rollNumber = isset($_GET['rollNumber']) ? $_GET['rollNumber'] : null;

    if (!$rollNumber) {
        echo json_encode(['error' => 'Roll number is required']);
        http_response_code(400);
        return;
    }

    $pdo = getDbConnection();
    $results = [];

    try {
        // Query both tables separately
        $stmt1 = $pdo->prepare('SELECT roll_number, semester, GPA, grade FROM semester01_3th_2022 WHERE roll_number = ?');
        $stmt1->execute([$rollNumber]);
        $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        // $stmt2 = $pdo->prepare('SELECT roll_number, semester, GPA, grade FROM semester02_3th_2022 WHERE roll_number = ?');
        // $stmt2->execute([$rollNumber]);
        // $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Combine results
        if ($result1) {
            $results[] = $result1;
        }
        // if ($result2) {
        //     $results[] = $result2;
        // }

        if ($results) {
            echo json_encode($results);
        } else {
            echo json_encode(['error' => 'No grades found for the specified semester']);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }
}

// Call the function to handle the request
get3rdSemesterResults();
?>
