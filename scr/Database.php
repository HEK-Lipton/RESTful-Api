<?php
// sets up the parameters for connecting to the SQL DBMS through pdo
// INFO: dbms login information has been hidden from git hub repository
$host = ;
$user = ;
$passwd = ;
$db = ;
$charset = "utf8mb4";
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ,
    PDO::ATTR_EMULATE_PREPARES => false);

// try statement to check the php connects to the DBMS
try {
    $pdo = new PDO($dsn,$user,$passwd,$opt);
} catch (PDOException $e) {
    echo 'Connection␣failed:␣', $e ->getMessage();
}
?>