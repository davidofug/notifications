<?php
$dbName = 'gagraphi_demo';
$dataSourceName = 'mysql:host=localhost;dbname='.$dbName;
$user = 'gagraphi_uza';
$key = 'bmehjBc9';

try {
    $connection = new PDO($dataSourceName, $user, $key);
    // set the PDO error mode to exception
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully";

    $connection = null;
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }