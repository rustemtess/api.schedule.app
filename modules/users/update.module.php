<?php

/**
 * Изменить права доступа у пользователя
 * @param int $user_id
 * @param int $permission_id
 * @return void
 */
function updatePermissionByUserId(int $user_id, int $permission_id): void {
    global $db;
    $query = $db->prepare("UPDATE `users` SET `permission_id` = ? WHERE user_id = ?");
    $query->bind_param('ii', $permission_id, $user_id);
    $query->execute();
}

?>