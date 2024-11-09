<?php

/**
 * Выводит результат в формате JSON и завершает выполнение скрипта.
 * 
 * @param array $arr Данные, которые будут закодированы в формат JSON.
 * @param int $statusCode HTTP статус код для ответа. По умолчанию 200.
 * @return void
 */
function json(array $arr, int $statusCode = 200): void {
    // Устанавливаем HTTP статус код
    http_response_code($statusCode);
    
    // Преобразуем массив в JSON
    $json = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Проверка на ошибки при кодировании в JSON
    if ($json === false) {
        // В случае ошибки кодирования, выводим ошибку и завершаем выполнение
        die(json_encode([
            'error' => 'Ошибка кодирования в JSON',
            'message' => json_last_error_msg()
        ]));
    }

    // Устанавливаем заголовки для JSON ответа
    header('Content-Type: application/json; charset=utf-8');
    
    // Выводим JSON и завершаем выполнение
    die($json);
}

?>
