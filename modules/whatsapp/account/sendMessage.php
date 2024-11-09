<?php

namespace Account;

/**
 * Функция отправки сообщения
 * 
 * Эта функция отправляет сообщение в указанный чат.
 * 
 * @param int $chatId Номер или ID чата.
 * @param string $message Сообщение, которое нужно отправить.
 * @param Type $type Тип чата, в который нужно отправить сообщение (например, "whatsapp" или "telegram").
 * 
 * @throws \Exception Если $chatId или $message не указаны.
 * @return void
 */
function sendMessage(
    int $chatId,
    string $message,
    Type $type
): void {

    // Проверка на наличие $chatId
    if (!$chatId) {
        throw new \Exception('ChatId not found');
    }

    // Проверка на наличие $message
    if (!$message) {
        throw new \Exception('Message not found');
    }

    // Формируем запрос и отправляем сообщение
    request(
        'sendMessage',
        array(
            'chatId' => $chatId . '@' . $type->value, // Формируем полный ID чата
            'message' => $message,
            'linkPreview' => false // Не показывать предварительный просмотр ссылок
        )
    );
}
