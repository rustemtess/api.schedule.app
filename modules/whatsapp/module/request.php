<?php

/**
 * Функция отправки POST запроса
 * Эта функция отправляет POST запрос на сервер с использованием данных в формате JSON.
 * 
 * @param string $method Метод, который будет использоваться в запросе.
 * @param array $payload Данные, которые будут отправлены в теле запроса.
 * @return string Ответ от сервера в виде строки.
 */
function request(string $method, array $payload): string {
    // Определяем параметры контекста для HTTP запроса
    $options = array(
        "http" => array(
            // Устанавливаем заголовок Content-Type для указания формата данных (JSON)
            "header" => "Content-Type: application/json\r\n",
            // Указываем метод запроса (POST)
            "method" => "POST",
            // Преобразуем массив данных в формат JSON для отправки в теле запроса
            "content" => json_encode($payload)
        )
    );

    // Создаем контекст для HTTP запроса на основе вышеуказанных настроек
    $context = stream_context_create($options);

    // Отправляем POST запрос с использованием file_get_contents, передаем метод и данные
    // _url($method) - это функция, которая возвращает URL для запроса, который зависит от переданного метода.
    $response = file_get_contents(_url($method), false, $context);

    // Возвращаем ответ от сервера
    return $response;
}
?>
