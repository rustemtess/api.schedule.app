<?php

// Устанавливаем временную зону
date_default_timezone_set('Etc/GMT-5');

// Устанавливаем заголовки для обработки запросов с разных источников
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 172800"); // Кэш на 2 дня
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обрабатываем OPTIONS запросы для CORS
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit;
}

try {
    // Разделяем URL и получаем маршрут
    $route = explode("/", $_SERVER["REQUEST_URI"]);
    $route = array_splice($route, 1); // Убираем первый элемент (api)

    // Подключаем необходимые файлы
    include_once 'router.php';          // Маршрутизатор
    include_once './modules/json.php';  // Модуль для работы с JSON
    include_once './database/db.php';   // Подключение к базе данных
    include_once './modules/file.php';  // Модуль для работы с файлами

    // Подключаем интерфейсы и модули
    include_once './modules/IModel.php'; // Интерфейс для моделей
    include_once './modules/date/index.php';   // Модуль работы с датой
    include_once './modules/users/index.php';  // Модуль пользователей
    include_once './modules/whatsapp/index.php'; // Модуль WhatsApp

    // Очищаем маршрут, убирая первый элемент
    $route = array_slice($route, 1);
    
    // Инициализируем маршрутизатор с путем
    $router = new Router($route);

    // Регистрируем модули и их маршруты
    $router->register(new Date(), 'date');
    $router->register(new Users(), 'users');
    $router->register(new WhatsApp(), 'whatsapp');

    // Проверяем, существует ли класс в маршруте
    if ($router->isPath($route[0])) {
        // Если существует, выполняем соответствующий метод
        return $router->execute();
    }

    json([
        'error' => 'Модуль не найден'
    ], 404);

} catch (Exception $e) {
    json([
        'error' => $e->getMessage()
    ]);
}

?>