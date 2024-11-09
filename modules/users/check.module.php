<?php

/**
 * Проверка почты и номера телефона
 * @param int $number
 * @return bool
 */
function isExistsNumber(int $number): bool {
    global $db;

    // Подготавливаем запрос для проверки наличия пользователя с таким номером
    $query = $db->prepare("SELECT `user_id` FROM `users` WHERE `user_number` = ?");
    
    // Привязываем параметр для запроса
    $query->bind_param('i', $number);
    
    // Выполняем запрос
    $query->execute();
    
    // Проверяем, вернул ли запрос результат
    $result = $query->get_result();
    
    // Если результат не пустой, значит, пользователь с таким номером существует
    return $result->num_rows > 0;
}

?>
