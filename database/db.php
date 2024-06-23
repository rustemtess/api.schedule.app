<?php

$jsonData = file_get_contents('./config/database.json');

// Проверка, был ли вызов file_get_contents успешным
if ($jsonData === false) {
    die('Error reading JSON file');
}

// Декодирование данных в формате JSON
$configDB = json_decode($jsonData, true);

// Проверка, был ли вызов json_decode успешным
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON: ' . json_last_error_msg());
}

$db = mysqli_connect(
    $configDB['host'],
    $configDB['login'],
    $configDB['password'],
    $configDB['dbName']
);

if(!$db) {
    die('Error connect database');
}

?>
