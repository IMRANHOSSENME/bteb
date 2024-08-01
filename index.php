<?php
header('Content-Type: application/json');

function get_db_connection() {
    // Create a new SQLite3 database connection
    $db = new SQLite3('results.db');
    return $db;
}

function get_results($roll_number, $table1, $table2 = null) {
    if (!$roll_number) {
        echo json_encode(['error' => 'Roll number is required']);
        http_response_code(400);
        exit();
    }

    $db = get_db_connection();
    $results = [];

    // Query the first table
    $stmt = $db->prepare("SELECT roll_number, semester, GPA, grade FROM $table1 WHERE roll_number = ?");
    $stmt->bindValue(1, $roll_number, SQLITE3_TEXT);
    $result1 = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result1) {
        $results[] = $result1;
    }

    // Query the second table if provided
    if ($table2) {
        $stmt = $db->prepare("SELECT roll_number, semester, GPA, grade FROM $table2 WHERE roll_number = ?");
        $stmt->bindValue(1, $roll_number, SQLITE3_TEXT);
        $result2 = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result2) {
            $results[] = $result2;
        }
    }

    $db->close();

    if (!empty($results)) {
        echo json_encode($results);
    } else {
        echo json_encode(['error' => 'No grades found for the specified semester']);
        http_response_code(404);
    }
}

function get_result() {
    $roll_number = isset($_GET['rollNumber']) ? $_GET['rollNumber'] : null;
    $examination = isset($_GET['examination']) ? $_GET['examination'] : null;
    $regulation = isset($_GET['regulation']) ? $_GET['regulation'] : null;

    if (!$roll_number || !$examination || !$regulation) {
        echo json_encode(['error' => 'Roll number, examination, and regulation are required']);
        http_response_code(400);
        exit();
    }

    $db = get_db_connection();

    $stmt1 = $db->prepare('
        SELECT roll_number, polytechnic_name, city, type, publish_date, referred_subjects, referred_p, referred_t, semester, examination, regulation, GPA, grade
        FROM semester01_4th_2022
        WHERE roll_number = ? AND examination = ? AND regulation = ?
    ');
    $stmt1->bindValue(1, $roll_number, SQLITE3_TEXT);
    $stmt1->bindValue(2, $examination, SQLITE3_TEXT);
    $stmt1->bindValue(3, $regulation, SQLITE3_TEXT);
    $result1 = $stmt1->execute()->fetchArray(SQLITE3_ASSOC);

    $stmt2 = $db->prepare('
        SELECT roll_number, polytechnic_name, city, type, publish_date, referred_subjects, referred_p, referred_t, semester, examination, regulation, GPA, grade
        FROM semester02_2nd_2022
        WHERE roll_number = ? AND examination = ? AND regulation = ?
    ');
    $stmt2->bindValue(1, $roll_number, SQLITE3_TEXT);
    $stmt2->bindValue(2, $examination, SQLITE3_TEXT);
    $stmt2->bindValue(3, $regulation, SQLITE3_TEXT);
    $result2 = $stmt2->execute()->fetchArray(SQLITE3_ASSOC);

    $results = [];
    if ($result1) {
        $results[] = $result1;
    }
    if ($result2) {
        $results[] = $result2;
    }

    $db->close();

    if ($results) {
        echo json_encode($results);
    } else {
        echo json_encode(['error' => 'No grades found for the specified criteria']);
        http_response_code(404);
    }
}

// Handle API request based on the URL path and parameters
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/'); // Remove leading and trailing slashes

// Check if path is empty or matches 'api'
if (empty($path) || $path === 'api') {
    $rollNumber = isset($_GET['rollNumber']) ? $_GET['rollNumber'] : null;
    $examination = isset($_GET['examination']) ? $_GET['examination'] : null;
    $regulation = isset($_GET['regulation']) ? $_GET['regulation'] : null;

    if ($rollNumber && $examination && $regulation) {
        get_result();
    } else {
        echo json_encode(['error' => 'Missing parameters']);
        http_response_code(400);
    }
} else {
    echo json_encode(['error' => 'Invalid endpoint']);
    http_response_code(404);
}
?>
