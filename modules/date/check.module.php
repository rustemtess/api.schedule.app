<?php

/**
 * Проверка, существует ли дата в базе данных
 * @param string $date
 * @return bool
 */
function isDate(string $date): bool {
    global $db;

    // Используем подготовленный запрос для защиты от SQL-инъекций
    $query = $db->prepare("SELECT 1 FROM `date` WHERE date_ymd = ?");
    if (!$query) {
        throw new Exception('Ошибка при подготовке запроса: ' . $db->error);
    }

    $query->bind_param('s', $date);
    $query->execute();
    $query->store_result();

    // Если результат запроса пуст, значит, дата не существует
    $exists = $query->num_rows > 0;
    $query->close();

    return $exists;
}

/**
 * Проверка, существует ли время в базе данных
 * @param string $time
 * @return bool
 */
function isTime(string $time): bool {
    global $db;

    // Используем подготовленный запрос для защиты от SQL-инъекций
    $query = $db->prepare("SELECT 1 FROM `time` WHERE time_hours = ?");
    if (!$query) {
        throw new Exception('Ошибка при подготовке запроса: ' . $db->error);
    }

    $query->bind_param('s', $time); // Время скорее всего строковое, не число
    $query->execute();
    $query->store_result();

    // Если результат запроса пуст, значит, время не существует
    $exists = $query->num_rows > 0;
    $query->close();

    return !$exists; // Возвращаем true, если время не существует
}

?>
