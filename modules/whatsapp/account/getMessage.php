<?php

namespace Account;

/**
 * Функция получение сообщение
 * @param int $chatId Номер или ID Чата
 * @param string $idMessage ID сообщение
 * @param Type $type Тип отправки
 * @return void
 */
function getMessage(
    int $chatId,
    string $idMessage,
    Type $type

): void {

    if(!$chatId) throw new \Exception('ChatId not found');
    if(!$idMessage) throw new \Exception('Message ID not found');

    echo request(
        'getMessage',
        array(
            'chatId' => $chatId.'@'.$type->value,
            'idMessage' => $idMessage
        )
    );

}