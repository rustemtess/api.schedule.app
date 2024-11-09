<?php

/**
 * Изменить права доступа у пользователя
 * @param int $user_id
 * @param int $permission_id
 * @return void
 * @throws Exception Если не удается обновить данные
 */
function updatePermissionByUserId(int $user_id, int $permission_id): void {
    global $db;

    // Подготавливаем запрос
    $query = $db->prepare("UPDATE `users` SET `permission_id` = ? WHERE user_id = ?");

    if (!$query) {
        throw new Exception("Ошибка подготовки запроса: " . $db->error);
    }

    // Привязываем параметры
    $query->bind_param('ii', $permission_id, $user_id);

    // Выполняем запрос
    if (!$query->execute()) {
        throw new Exception("Ошибка выполнения запроса: " . $query->error);
    }

    // Проверяем, были ли изменения в базе данных
    if ($query->affected_rows === 0) {
        throw new Exception("Нет изменений: возможно, пользователь с таким ID не найден.");
    }

    // Закрываем подготовленный запрос
    $query->close();
}

?>
