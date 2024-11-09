<?php

/**
 * Получить все часы
 * @return array
 */
function getTimeList(): array {
    global $db;

    // Подготовка и выполнение запроса для получения всех часов
    $query = "SELECT `time_id` AS `id`, `time_hours` AS `hour` FROM `time`";
    $result = $db->query($query);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Получить ID даты по Дате
 * @param string $date
 * @return int
 * @throws Exception
 */
function getIdByDate(string $date): int {
    global $db;

    // Подготовка запроса для получения ID даты
    $query = $db->prepare("SELECT `date_id` FROM `date` WHERE `date_ymd` = ?");
    $query->bind_param('s', $date);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    if (!$result) {
        throw new Exception('ID Даты не найдена');
    }

    return (int)$result['date_id'];
}

/**
 * Получить ID времени по Времени
 * @param string $time
 * @return int
 * @throws Exception
 */
function getIdByTime(string $time): int {
    global $db;

    // Разбиваем строку времени на часы и минуты
    $timeParts = explode(':', $time);
    if (count($timeParts) !== 2) {
        throw new Exception('Неправильное время');
    }

    list($hours, $minutes) = $timeParts;
    $hours = ltrim($hours, '0');  // Убираем ведущий ноль

    // Подготовка запроса для поиска времени по шаблону
    $likePattern = $hours . ':%';
    $query = $db->prepare("SELECT `time_id`, `time_hours` FROM `time` WHERE `time_hours` LIKE ? ORDER BY `time_hours` ASC");
    $query->bind_param('s', $likePattern);
    $query->execute();
    $result = $query->get_result();

    // Переменная для хранения ID предыдущего времени
    $previousId = null;

    while ($row = $result->fetch_assoc()) {
        list($currentHours, $currentMinutes) = explode(':', $row['time_hours']);

        if ($currentHours > $hours || ($currentHours == $hours && $currentMinutes > $minutes)) {
            if ($previousId === null) {
                throw new Exception('Время не найдено');
            }
            return $previousId;
        }

        $previousId = $row['time_id'];
    }

    if ($previousId === null) {
        throw new Exception('Время не найдено');
    }

    return $previousId;
}

/**
 * Получить даты и времена
 * @param string|null $text
 * @return array
 * @throws Exception
 */
function getOrSearchDateTimeList(?string $text = ''): array {
    global $db;

    // Подготовка базового запроса
    $baseQuery = "SELECT 
                    time.time_hours, 
                    date.date_ymd, 
                    meet_data.meet_data_text, 
                    meet_data.meet_data_time, 
                    meet_data.meet_data_file, 
                    colors.color_rgb, 
                    meet_data.meet_data_id 
                  FROM `time`
                  LEFT JOIN `meeting` ON time.time_id = meeting.time_id
                  LEFT JOIN `date` ON meeting.date_id = date.date_id
                  LEFT JOIN `meet_data` ON meeting.meet_id = meet_data.meet_id
                  LEFT JOIN `colors` ON colors.color_id = meet_data.color_id
                  LEFT JOIN `users` ON meet_data.user_id = users.user_id";

    // Добавление условий для поиска
    if ($text !== '') {
        $text = '%' . mysqli_real_escape_string($db, $text) . '%';
        $baseQuery .= " WHERE meet_data.meet_data_text LIKE ? AND (meet_data.user_id IS NULL OR users.user_id IS NOT NULL)";
    } else {
        $baseQuery .= " WHERE meet_data.user_id IS NULL OR users.user_id IS NOT NULL";
    }

    // Добавление сортировки
    $baseQuery .= " ORDER BY STR_TO_DATE(time.time_hours, '%H:%i') ASC";

    // Подготовка запроса и выполнение
    $query = $db->prepare($baseQuery);
    if ($text !== '') {
        $query->bind_param('s', $text);
    }
    $query->execute();
    $result = $query->get_result();

    if (!$result) {
        throw new Exception("Ошибка выполнения запроса: " . $db->error);
    }

    $dateTimeList = [];

    while ($row = $result->fetch_assoc()) {
        $time = $row['time_hours'];
        $date = isset($row['date_ymd']) ? date('d', strtotime($row['date_ymd'])) : null;
        $text = $row['meet_data_text'] ?? null;
        $meetId = $row['meet_data_id'] ?? null;
        $meetTime = $row['meet_data_time'] ?? null;
        $meetFile = $row['meet_data_file'] ?? null;
        $meetColor = $row['color_rgb'] ?? null;

        // Группируем данные по времени
        if (!isset($dateTimeList[$time])) {
            $dateTimeList[$time] = [
                'time' => $time,
                'dateObjects' => []
            ];
        }

        // Группируем данные по дате
        if ($date) {
            $dateObjectIndex = array_search($date, array_column($dateTimeList[$time]['dateObjects'], 'date'));
            if ($dateObjectIndex === false) {
                $dateTimeList[$time]['dateObjects'][] = [
                    'day' => $date,
                    'date' => $row['date_ymd'],
                    'timeObjects' => []
                ];
                $dateObjectIndex = count($dateTimeList[$time]['dateObjects']) - 1;
            }

            // Добавляем данные о встрече
            if ($text) {
                $dateTimeList[$time]['dateObjects'][$dateObjectIndex]['timeObjects'][] = [
                    'id' => $meetId,
                    'text' => $text,
                    'time' => $meetTime,
                    'rgb' => $meetColor,
                    'fileUrl' => $meetFile
                ];
            }
        }
    }

    // Преобразуем вложенные ассоциативные массивы в индексированные массивы
    foreach ($dateTimeList as &$timeData) {
        foreach ($timeData['dateObjects'] as &$dateData) {
            $dateData['timeObjects'] = array_values($dateData['timeObjects']);
        }
    }

    return array_values($dateTimeList);
}




/**
 * Получить все цвета
 * @return array
 */
function getColors(): array {
    global $db;

    $query = "SELECT `color_id` AS id, `color_rgb` AS rgb FROM `colors`";
    $result = $db->query($query);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Получить информацию о времени
 * @param int $time_id
 * @return array
 * @throws Exception
 */
function getTimeInfo(int $time_id): array {
    global $db;

    // Подготовка запроса для получения информации о встрече
    $query = $db->prepare(
        "SELECT 
            users.user_name AS name,
            users.user_surname AS surname,
            users.user_middlename AS middlename,
            users.user_number AS number,
            meet_data.meet_data_registered AS timeRegistered,
            meet_data.meet_id AS meetId
         FROM `meet_data`
         JOIN `users` ON users.user_id = meet_data.user_id
         WHERE meet_data.meet_data_id = ?"
    );
    $query->bind_param('i', $time_id);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    if (!$result) {
        throw new Exception('Неправильный ID времени');
    }

    return $result;
}

?>
