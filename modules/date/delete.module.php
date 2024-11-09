<?php

/**
 * Удалить время по ID и по ID пользователя
 * @param int $time_id
 * @param int $user_id
 * @return void
 * @throws Exception
 */
function deleteTimeById(int $time_id, int $user_id): void {
    global $db;

    // Подготовка и выполнение запроса для получения meet_id
    $query = $db->prepare("SELECT meet_id FROM `meet_data` WHERE meet_data_id = ?");
    if (!$query) {
        throw new Exception('Ошибка при подготовке запроса для получения meet_id: ' . $db->error);
    }
    
    $query->bind_param('i', $time_id);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();
    $query->close();

    if (!$data) {
        throw new Exception('ID времени не найден');
    }

    // Удаляем запись из meet_data
    $query = $db->prepare("DELETE FROM `meet_data` WHERE meet_data_id = ?");
    if (!$query) {
        throw new Exception('Ошибка при подготовке запроса на удаление из meet_data: ' . $db->error);
    }

    $query->bind_param('i', $time_id);
    if (!$query->execute()) {
        $query->close();
        throw new Exception('Ошибка при выполнении запроса на удаление: ' . $query->error);
    }
    $query->close();

    // Обновляем запись в meeting с указанием ID пользователя, который удаляет
    $meet_id = (int)$data['meet_id'];
    $query = $db->prepare("UPDATE `meeting` SET delete_user_id = ? WHERE meet_id = ?");
    if (!$query) {
        throw new Exception('Ошибка при подготовке запроса на обновление в meeting: ' . $db->error);
    }

    $query->bind_param('ii', $user_id, $meet_id);
    if (!$query->execute()) {
        $query->close();
        throw new Exception('Ошибка при выполнении запроса на обновление: ' . $query->error);
    }

    $query->close();
}
