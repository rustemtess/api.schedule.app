<?php

// Подключение заголовков
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 172800"); // кэш на 2 дня
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Проверка на разрешенность заголовков
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit;
};

// Разделяем URL
$route = explode("/", $_SERVER["REQUEST_URI"]);
$route = array_splice($route, 1);

include_once 'router.php';
include_once './modules/json.php';
include_once './database/db.php';
include_once './modules/file.php';

// Подключаем интерфейс
include_once './modules/IModel.php';

// Подключаем все модули
include_once './modules/date/index.php';
include_once './modules/users/index.php';
include_once './modules/whatsapp/index.php';

$router = new Router($route);
$router->register(new Date(), 'date');
$router->register(new Users(), 'users');
$router->register(new WhatsApp(), 'whatsapp');
if($router->isPath($route[0])) {
    return $router->execute();
}

die('Ошибка модуль не найден');

?>