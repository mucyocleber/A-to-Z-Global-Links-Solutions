<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

try {
    $pdo = getConnection();
    
    // First, let's test a simple query
    echo "<h3>Testing simple applications query:</h3>";
    $simple = $pdo->query("SELECT COUNT(*) as count FROM applications");
    $count = $simple->fetch();
    echo "Applications count: " . $count['count'] . "<br><br>";
    
    // Test each table individually
    echo "<h3>Testing individual tables:</h3>";
    
    try {
        $users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
        echo "Users count: " . $users['count'] . "<br>";
    } catch (Exception $e) {
        echo "Users table error: " . $e->getMessage() . "<br>";
    }
    
    try {
        $services = $pdo->query("SELECT COUNT(*) as count FROM services")->fetch();
        echo "Services count: " . $services['count'] . "<br>";
    } catch (Exception $e) {
        echo "Services table error: " . $e->getMessage() . "<br>";
    }
    
    try {
        $statuses = $pdo->query("SELECT COUNT(*) as count FROM application_statuses")->fetch();
        echo "Application statuses count: " . $statuses['count'] . "<br>";
    } catch (Exception $e) {
        echo "Application statuses table error: " . $e->getMessage() . "<br>";
    }
    
    try {
        $countries = $pdo->query("SELECT COUNT(*) as count FROM destination_countries")->fetch();
        echo "Destination countries count: " . $countries['count'] . "<br>";
    } catch (Exception $e) {
        echo "Destination countries table error: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><h3>Testing basic join:</h3>";
    
    // Test basic join
    try {
        $basic_join = $pdo->query("
            SELECT a.id, a.application_number, u.first_name, u.last_name 
            FROM applications a 
            JOIN users u ON a.user_id = u.id 
            LIMIT 5
        ");
        $results = $basic_join->fetchAll();
        echo "Basic join successful. Found " . count($results) . " records:<br>";
        foreach ($results as $row) {
            echo "- " . $row['application_number'] . ": " . $row['first_name'] . " " . $row['last_name'] . "<br>";
        }
    } catch (Exception $e) {
        echo "Basic join error: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage();
}
?>