<?php

/**
 * Удалить пользователя по ID
 * @param int $user_id
 * @return void
 */
function deleteUserById(int $user_id): void {
    global $db;
    
    // Подготавливаем DELETE запрос
    $query = $db->prepare("DELETE FROM `users` WHERE `user_id` = ?");
    
    // Привязываем параметр user_id к запросу
    $query->bind_param('i', $user_id);
    
    // Выполняем запрос
    $query->execute();
}

?>