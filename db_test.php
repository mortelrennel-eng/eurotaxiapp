<?php
// DB Diagnostic Script
echo "<h1>DB Diagnostic</h1>";

try {
    $env = file_get_contents('.env');
    preg_match('/DB_HOST=(.*)/', $env, $host);
    preg_match('/DB_DATABASE=(.*)/', $env, $db);
    preg_match('/DB_USERNAME=(.*)/', $env, $user);
    preg_match('/DB_PASSWORD=(.*)/', $env, $pass);
    
    $host = trim($host[1] ?? '127.0.0.1');
    $db = trim($db[1] ?? '');
    $user = trim($user[1] ?? '');
    $pass = trim($pass[1] ?? '');
    
    echo "Attempting to connect to $db at $host as $user...<br>";
    
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<b>CONNECTION SUCCESSFUL!</b><br>";
    
    $query = $conn->query("SHOW TABLES");
    echo "Tables found: " . count($query->fetchAll()) . "<br>";
    
} catch (Exception $e) {
    echo "<b>CONNECTION FAILED:</b> " . $e->getMessage();
}
?>
