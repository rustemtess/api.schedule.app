<?php

// Устанавливаем временную зону для работы с датой и временем
date_default_timezone_set('Etc/GMT-5');

/**
 * Получить список номеров пользователей.
 * Эта функция выполняет SQL запрос, который извлекает ID и номера пользователей из базы данных.
 * 
 * @return array Массив с данными пользователей (ID и номера).
 */
function getNumbers(): array {
    global $db;

    // Выполняем запрос для получения всех номеров пользователей
    $numbers = $db->query(
        "SELECT user_id, user_number FROM users"
        )->fetch_all(MYSQLI_ASSOC);

    return $numbers;
}

/**
 * Получить список ФИО и номеров пользователей.
 * Эта функция выполняет SQL запрос, который извлекает номера и ФИО пользователей.
 * 
 * @return array Массив с данными пользователей (номер, имя, фамилия, отчество).
 */
function getFullNameAndNumbers(): array {
	global $db;

    // Выполняем запрос для получения номеров и ФИО пользователей
    $result = $db->query(
        "SELECT user_number, user_name, user_surname, user_middlename FROM users"
        )->fetch_all(MYSQLI_ASSOC);

    return $result;
}

/**
 * Получить встречи, которые будут в течение следующих 50 минут.
 * Эта функция выполняет SQL запрос для получения встреч, которые запланированы на ближайшие 50 минут.
 * После выполнения запроса, она обновляет записи в базе данных, устанавливая флаг уведомления о встрече.
 * 
 * @return array Массив с данными о встречах (ID, время, текст, пользователи).
 */
function getMeetNext50Min(): array {
    global $db;

    // Получаем текущую дату и время в формате 'Y-m-d' и 'H:i'
    $currentDay = date('Y-m-d');
    $currentHM = date('H:i');

    // Формируем SQL запрос для получения встреч, которые будут в течение следующих 50 минут
    $query = "
        SELECT meet_data.meet_id as id, meet_data.meet_data_time as time, meet_data.meet_data_text as text, meet_data.meet_users as users
        FROM date
        INNER JOIN meeting ON date.date_id = meeting.date_id
        INNER JOIN meet_data ON meeting.meet_id = meet_data.meet_id
        WHERE date.date_ymd = '$currentDay' 
          AND meet_data.meet_data_notified = 0 
          AND meet_data.meet_data_time BETWEEN '$currentHM' AND ADDTIME('$currentHM', '00:51:00')
        ORDER BY meet_data.meet_data_time ASC
    ";

    // Выполняем запрос на получение встреч
    $meeting = $db->query($query);

    // Проверка на ошибки выполнения запроса
    if (!$meeting)
        throw new Exception("Query failed: " . $db->error);

    // Получаем данные о встречах в массив
    $meetingData = $meeting->fetch_all(MYSQLI_ASSOC);

    // Формируем запрос на обновление, чтобы отметить встречи как уведомленные
    $updateQuery = "
        UPDATE meet_data
        INNER JOIN meeting ON meet_data.meet_id = meeting.meet_id
        INNER JOIN date ON meeting.date_id = date.date_id
        SET meet_data_notified = 1
        WHERE date.date_ymd = '$currentDay' 
          AND meet_data.meet_data_notified = 0 
          AND meet_data.meet_data_time BETWEEN '$currentHM' AND ADDTIME('$currentHM', '00:51:00')
    ";

    // Выполняем запрос на обновление
    $updateResult = $db->query($updateQuery);

    // Проверка на ошибки выполнения запроса на обновление
    if (!$updateResult)
        throw new Exception("Update query failed: " . $db->error);

    // Возвращаем массив данных о встречах
    return $meetingData;
}

?>
