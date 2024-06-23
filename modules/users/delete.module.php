<?php

/**
 * Удалить пользователя по ID
 * @param int $user_id
 * @return void
 */
function deleteUserById(int $user_id): void {
    global $db;
    $query = $db->prepare("DELETE FROM `users` WHERE `user_id` = ?");
    $query->bind_param('i', $user_id);
    $query->execute();
}

?>