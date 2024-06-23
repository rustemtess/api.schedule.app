<?php

/**
 * Удалить время по ID и по ID пользователя
 * @param int $time_id
 * @return void
 */
function deleteTimeById(int $time_id, int $user_id): void {
    global $db;

    // Подготовка первого запроса
    $query = $db->prepare("SELECT meet_id FROM `meet_data` WHERE meet_data_id = ?");
    if ($query === false) {
        throw new Exception('Ошибка при подготовке запроса: ' . $db->error);
    }
    $query->bind_param('i', $time_id);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();
    $query->close();

    if (!$data) {
        throw new Exception('ID времени не найден');
    }

    // Подготовка запроса на удаление
    $query = $db->prepare("DELETE FROM `meet_data` WHERE meet_data_id = ?");
    if ($query === false) {
        throw new Exception('Ошибка при подготовке запроса: ' . $db->error);
    }
    $query->bind_param('i', $time_id);
    if (!$query->execute()) {
        $query->close();
        throw new Exception('Ошибка при выполнении запроса на удаление: ' . $query->error);
    }
    $query->close();

    // Подготовка запроса на обновление
    $meet_id = intval($data['meet_id']); // Сохраняем результат в переменную
    $query = $db->prepare("UPDATE `meeting` SET delete_user_id = ? WHERE meet_id = ?");
    if ($query === false) {
        throw new Exception('Ошибка при подготовке запроса: ' . $db->error);
    }
    $query->bind_param('ii', $user_id, $meet_id); // Передаем переменную в bind_param
    if (!$query->execute()) {
        $query->close();
        throw new Exception('Ошибка при выполнении запроса на обновление: ' . $query->error);
    }
    $query->close();
}
