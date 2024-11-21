<?php

include_once "./modules/users/get.module.php";

/**
 * Выполняет POST запрос
 * @param array $arr
 * @return void
 */
function post(array $arr): void
{
    // URL для POST запроса
    $url = "https://schedule.choices.kz/api/whatsapp/only";

    // Данные для отправки в запросе
    $data = [
        "id" => $arr[0],
        "time" => $arr[1],
        "text" => $arr[2],
        "users" => $arr[3],
    ];

    // Инициализация cURL
    $ch = curl_init($url);

    // Настройка параметров cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Выполнение запроса и получение ответа
    $response = curl_exec($ch);

    // Проверка на ошибки
    if (curl_errno($ch)) {
        echo "Ошибка: " . curl_error($ch);
    } else {
        // Вывод ответа
        echo "Ответ: " . $response;
    }

    // Закрытие cURL
    curl_close($ch);
}

/**
 * Создает запись даты и времени с информацией
 * @param string $date
 * @param string $time
 * @param array $data
 * @return void
 */
function createDate(string $date, string $time, array $data): void
{
    global $db;

    // Проверяем количество данных
    if (count($data) !== 5) {
        throw new Exception("Не достаточно данных");
    }

    // Если дата не существует, добавляем в базу
    if (!isDate($date)) {
        $query = $db->prepare("INSERT INTO `date` (`date_ymd`) VALUES (?)");
        if (!$query) {
            throw new Exception("Ошибка при подготовке запроса: " . $db->error);
        }
        $query->bind_param("s", $date);
        if (!$query->execute()) {
            throw new Exception(
                "Ошибка при выполнении запроса: " . $query->error
            );
        }
    }

    // Получаем ID даты и времени
    $dateId = getIdByDate($date);
    $timeId = getIdByTime($time);

    // Проверяем существование даты и времени в базе
    if ($dateId && $timeId) {
        $query = $db->prepare(
            "INSERT INTO `meeting` (`date_id`, `time_id`) VALUES (?, ?)"
        );
        if (!$query) {
            throw new Exception("Ошибка при подготовке запроса: " . $db->error);
        }
        $query->bind_param("ii", $dateId, $timeId);
        if (!$query->execute()) {
            throw new Exception(
                "Ошибка при выполнении запроса: " . $query->error
            );
        }

        // Получаем ID встречи
        $meetId = $query->insert_id;
        $userId = getUser($data[2])["id"];

        // Если есть файл, загружаем его
        $meetFile = count($data[1]) > 0 ? uploadFile($data[1]) : "";

        // Вставляем данные о встрече
        $query = $db->prepare(
            "INSERT INTO `meet_data`
            (`meet_id`, `meet_data_time`, `meet_data_text`, `meet_data_file`, `color_id`, `user_id`, `meet_users`)
            VALUES (?, ?, ?, NULLIF(?, ''), ?, ?, ?)"
        );
        if (!$query) {
            throw new Exception("Ошибка при подготовке запроса: " . $db->error);
        }
        $query->bind_param(
            "isssiis",
            $meetId,
            $time,
            $data[0],
            $meetFile,
            $data[3],
            $userId,
            $data[4]
        );
        if (!$query->execute()) {
            throw new Exception(
                "Ошибка при выполнении запроса: " . $query->error
            );
        }

        // Если дата сегодняшняя, отправляем POST запрос на отправку уведомления в WhatsApp
        if ($date === date("Y-m-d")) {
            post([$meetId, $time, $data[0], $data[4]]);
        }
    }
}

/**
 * Создает запись времени
 * @param string $time
 * @return void
 */
function createTime(string $time): void
{
    global $db;

    // Если время не существует в базе
    if (!isTime($time)) {
        $query = $db->prepare("INSERT INTO `time` (`time_hours`) VALUES (?)");
        if (!$query) {
            throw new Exception("Ошибка при подготовке запроса: " . $db->error);
        }
        $query->bind_param("s", $time);
        if (!$query->execute()) {
            throw new Exception(
                "Ошибка при выполнении запроса: " . $query->error
            );
        }
    }
}

?>
