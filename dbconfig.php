<?php
    //Test
    $host = 'localhost';
    $db_name = 'AEGF_TEST_DELTA';
    $username = 'postgres';
    $password = 'pass@123';
    $port = '5432';
    $conn;

    try { 
        $conn = new PDO('pgsql:host='.$host.';port='.$port.';dbname='.$db_name.';user='.$username.';password='.$password);
        //$conn = new PDO('pgsql:host=' . $host . ';dbname=' . $db_name, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        echo 'Connection Error: ' . $e->getMessage();
    }
?>