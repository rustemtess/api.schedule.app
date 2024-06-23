<?php

namespace Account;

/**
 * Функция отправки сообщение
 * @param int $chatId Номер или ID Чата
 * @param string $message Сообщение
 * @param Type $type Тип отправки
 * @return void
 */
function sendMessage(
    int $chatId,
    string $message,
    Type $type
): void {

    if(!$chatId) throw new \Exception('ChatId not found');
    if(!$message) throw new \Exception('Message not found');

    /*echo*/ request(
        'sendMessage',
        array(
            'chatId' => $chatId.'@'.$type->value,
            'message' => $message
        )
    );

}