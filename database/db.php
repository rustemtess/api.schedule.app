<?php

// Чтение конфигурационного файла JSON для получения данных MySQL
$jsonData = file_get_contents('./config/database.json');

// Проверка, был ли вызов file_get_contents успешным
if ($jsonData === false) {
    die('Ошибка чтения JSON файла');
}

// Декодирование данных из формата JSON в массив PHP
$configDB = json_decode($jsonData, true);

// Проверка на ошибки при декодировании JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Ошибка декодирования JSON: ' . json_last_error_msg());
}

// Проверка, что все необходимые параметры конфигурации присутствуют
$requiredKeys = ['host', 'login', 'password', 'dbName'];
foreach ($requiredKeys as $key) {
    if (!isset($configDB[$key])) {
        die('Отсутствует обязательный параметр конфигурации: ' . $key);
    }
}

// Подключение к базе данных MySQL
$db = mysqli_connect(
    $configDB['host'],
    $configDB['login'],
    $configDB['password'],
    $configDB['dbName']
);

// Проверка успешности подключения
if (!$db) {
    die('Ошибка подключения к базе данных: ' . mysqli_connect_error());
}

?>
