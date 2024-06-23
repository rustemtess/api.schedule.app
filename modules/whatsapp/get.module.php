<?php

date_default_timezone_set('Etc/GMT-5');


/**
 * Получить список номеров
 * @return array 
 */
function getNumbers(): array {
    global $db;
    $numbers = $db->query(
        "SELECT user_number FROM users"
        )->fetch_all(MYSQLI_ASSOC);
    return $numbers;
}

/**
 * Получить встречи до 30 минут
 * @return array
 */
function getMeetNext30Min(): array {
    global $db;
    $currentDay = date('Y-m-d');
    $currentHM = date('H:i');

    // Используем интервал времени для вычисления следующих 30 минут
    $query = "
        SELECT meet_data.meet_data_time as time, meet_data.meet_data_text as text
        FROM date
        INNER JOIN meeting ON date.date_id = meeting.date_id
        INNER JOIN meet_data ON meeting.meet_id = meet_data.meet_id
        WHERE date.date_ymd = '$currentDay' 
          AND meet_data.meet_data_notified = 0 AND meet_data.meet_data_time BETWEEN '$currentHM' AND ADDTIME('$currentHM', '00:31:00')
        ORDER BY meet_data.meet_data_time ASC
    ";

    $meeting = $db->query($query);

    // Проверка на ошибки выполнения запроса
    if (!$meeting) {
        throw new Exception("Query failed: " . $db->error);
    }

    // Получаем данные о встречах в массив
    $meetingData = $meeting->fetch_all(MYSQLI_ASSOC);

    $updateQuery = "
        UPDATE meet_data
        INNER JOIN meeting ON meet_data.meet_id = meeting.meet_id
        INNER JOIN date ON meeting.date_id = date.date_id
        SET meet_data_notified = 1
        WHERE date.date_ymd = '$currentDay' 
          AND meet_data.meet_data_notified = 0 
          AND meet_data.meet_data_time BETWEEN '$currentHM' AND ADDTIME('$currentHM', '00:30:00')
    ";

    // Выполняем запрос на обновление
    $updateResult = $db->query($updateQuery);

    // Проверка на ошибки выполнения запроса на обновление
    if (!$updateResult) {
        throw new Exception("Update query failed: " . $db->error);
    }

    // Возвращаем массив данных о встречах
    return $meetingData;
}




?>