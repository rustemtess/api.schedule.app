<?php

/**
 * Функция отправки POST запроса
 */
function request(string $method, array $payload): string {

    $options = array(
        "http" => array(
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => json_encode($payload)
        )
    );

    $context = stream_context_create($options);

    $response = file_get_contents(_url($method), false, $context);
    return $response;
}