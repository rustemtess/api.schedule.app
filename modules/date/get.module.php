<?php

/**
 * Получить все часы
 * @return array
 */
function getTimeList(): array {
    global $db;
    return $db->query(
        "SELECT `time_id` as `id`, `time_hours` as `hour`  FROM `time`"
    )->fetch_all(MYSQLI_ASSOC);
}

/**
 * Получить ID даты по Дате
 * @param string $date
 * @return int
 */
function getIdByDate(string $date): int {
    global $db;
    $query = $db->prepare("SELECT `date_id` FROM `date` WHERE `date_ymd` = ?");
    $query->bind_param('s', $date);
    $query->execute();
    $result = $query->get_result();
    $result = $result->fetch_assoc();
    if(!isset($result['date_id'])) throw new Exception('ID Даты не найдена');
    return $result['date_id'];
}

/**
 * Получить ID времени по Времени
 * @param string $time
 * @return int
 */
function getIdByTime(string $time): int {
    global $db;

    $time = explode(':', $time);
    if(count($time) != 2) throw new Exception('Неправильная время');
    // Разбиваем время на часы и минуты
    list($hours, $minutes) = $time;
    $hours = ltrim($hours, '0');
    // Подготавливаем часть запроса для LIKE
    $likePattern = $hours . ':%';
    // Получаем все времена, которые начинаются с указанных часов
    $query = $db->prepare("SELECT `time_id`, `time_hours` FROM `time` WHERE `time_hours` LIKE ? ORDER BY `time_hours` ASC");
    $query->bind_param('s', $likePattern);
    $query->execute();
    $result = $query->get_result();
    // Инициализируем переменную для хранения ID предыдущего времени
    $previousId = null;
    // Проходимся по результатам
    while ($row = $result->fetch_assoc()) {
        // Разбиваем текущее время на часы и минуты
        list($currentHours, $currentMinutes) = explode(':', $row['time_hours']);
        // Сравниваем текущее время с указанным временем
        // Если текущее время больше указанного, возвращаем предыдущее время
        if ($currentHours > $hours || ($currentHours == $hours && $currentMinutes > $minutes)) {
            // Если нет предыдущего ID, значит это первое время и возвращаем исключение
            if ($previousId === null) {
                throw new Exception('Время не найдено');
            }
            return $previousId;
        }
        // Обновляем предыдущее время и его ID
        $previousId = $row['time_id'];
    }
    // Если не нашли ни одного времени, возвращаем последнее найденное время
    if ($previousId === null) {
        throw new Exception('Время не найдено');
    }
    return $previousId;
}

/**
 * Получить даты и времена
 * @return array
 */
function getOrSearchDateTimeList(?string $text = ''): array {
    global $db;

    // Получаем данные из базы данных с использованием LEFT JOIN
    if ($text === '') {
        $result = $db->query(
            "SELECT time.time_hours, date.date_ymd, 
            meet_data.meet_data_text, meet_data.meet_data_time, meet_data.meet_data_file, colors.color_rgb, meet_data.meet_data_id, users.user_id 
            FROM `time`
            LEFT JOIN `meeting` ON time.time_id = meeting.time_id
            LEFT JOIN `date` ON meeting.date_id = date.date_id
            LEFT JOIN `meet_data` ON meeting.meet_id = meet_data.meet_id
            LEFT JOIN `colors` ON colors.color_id = meet_data.color_id
            LEFT JOIN `users` ON meet_data.user_id = users.user_id
            WHERE meet_data.user_id IS NULL OR users.user_id IS NOT NULL
            ORDER BY STR_TO_DATE(time.time_hours, '%H:%i') ASC"
        );
    } else {
        // Используйте mysqli_real_escape_string для безопасного вставки переменной в строку запроса
        $text = mysqli_real_escape_string($db, $text);
        // Добавьте символы подстановки % вокруг переменной $text
        $text = '%' . $text . '%';
        $result = $db->query(
            "SELECT time.time_hours, date.date_ymd, 
                    meet_data.meet_data_text, meet_data.meet_data_time, meet_data.meet_data_file, colors.color_rgb, meet_data.meet_data_id 
            FROM `time`
            LEFT JOIN `meeting` ON time.time_id = meeting.time_id
            LEFT JOIN `date` ON meeting.date_id = date.date_id
            LEFT JOIN `meet_data` ON meeting.meet_id = meet_data.meet_id
            LEFT JOIN `colors` ON colors.color_id = meet_data.color_id
            LEFT JOIN `users` ON meet_data.user_id = users.user_id
            WHERE meet_data.meet_data_text LIKE '$text' AND meet_data.user_id IS NULL OR users.user_id IS NOT NULL
            ORDER BY STR_TO_DATE(time.time_hours, '%H:%i') ASC"
        );
    }
    

    // Проверяем успешность выполнения запроса
    if (!$result) {
        throw new Exception("Failed to fetch data: " . $db->error);
    }

    // Инициализируем массив для хранения результирующих данных
    $dateTimeList = [];

    // Проходимся по результатам запроса
    while ($row = $result->fetch_assoc()) {
        // Извлекаем данные из строки результата
        $time = $row['time_hours'];
        $date = isset($row['date_ymd']) ? date('d', strtotime($row['date_ymd'])) : null;
        $text = isset($row['meet_data_text']) ? $row['meet_data_text'] : null;
        $meetId = isset($row['meet_data_id']) ? $row['meet_data_id'] : null;
        $meetTime = isset($row['meet_data_time']) ? $row['meet_data_time'] : null;
        $meetFile = isset($row['meet_data_file']) ? $row['meet_data_file'] : null;
        $meetColor = isset($row['color_rgb']) ? $row['color_rgb'] : null;

        // Проверяем, существует ли уже запись для данного времени
        if (!isset($dateTimeList[$time])) {
            // Если нет, создаем новую запись для времени
            $dateTimeList[$time] = [
                'time' => $time,
                'dateObjects' => []
            ];
        }

        // Проверяем, существует ли уже запись для данной даты
        if ($date) {
            $dateObjectIndex = array_search($date, array_column($dateTimeList[$time]['dateObjects'], 'date'));
            if ($dateObjectIndex === false) {
                // Если нет, создаем новую запись для даты
                $dateTimeList[$time]['dateObjects'][] = [
                    'date' => $date,
                    'timeObjects' => []
                ];
                $dateObjectIndex = count($dateTimeList[$time]['dateObjects']) - 1;
            }

            // Добавляем текст в список для данной даты и времени
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

    // Преобразуем ассоциативный массив в индексированный массив
    $dateTimeList = array_values($dateTimeList);

    // Возвращаем результирующий массив
    return $dateTimeList;
}

/**
 * Получить цвета
 * @return array
 */
function getColors(): array {
    global $db;
    return $db->query("SELECT `color_id` as id, `color_rgb` as rgb FROM `colors`")->fetch_all(MYSQLI_ASSOC);
}

function getTimeInfo(int $time_id): array {
    global $db;
    $query = $db->prepare(
        "SELECT users.user_name as name,
        users.user_surname as surname,
        users.user_middlename as middlename,
        users.user_number as number,
        meet_data.meet_data_registered as timeRegistered
        FROM meet_data, users WHERE meet_data.meet_data_id = ? 
        AND users.user_id = meet_data.user_id");
    $query->bind_param('i', $time_id);
    $query->execute();
    $result = $query->get_result();
    $result = $result->fetch_assoc();
    if(!$result) throw new Exception('Неправильный ID времени');
    return $result;
}



?>