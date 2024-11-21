<?php

/**
 * POST запрос
 * @param array $arr
 * @return void
 */
function post($arr): void
{
    $url = "https://schedule.choices.kz/api/whatsapp/sendmsg";

    $data = [
        "number" => $arr[0],
        "message" => $arr[1],
    ];

    // Инициализация cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Выполнение запроса и получение ответа
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        // Обработка ошибки cURL
        echo "Ошибка: " . curl_error($ch);
    } else {
        // Вывод ответа
        echo "Ответ: " . $response;
    }

    // Закрытие cURL
    curl_close($ch);
}

/**
 * Регистрация пользователя
 * @param array $data
 * @return void
 */
function register(array $data): void
{
    global $db;

    if (count($data) !== 8) {
        throw new Exception("Не достаточно данных");
    }

    // Валидация email
    $email = trim($data["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Некорректный email");
    }

    $number = preg_replace("/[^0-9]/", "", $data["number"]); // Оставляем только цифры
    if (strlen($number) !== 11) {
        throw new Exception("Некорректный номер телефона");
    }

    // Проверка существования номера
    if (isExistsNumber($number)) {
        throw new Exception("Телефон номер уже зарегистрирован ");
    }

    // Хеширование пароля с использованием password_hash
    $passwordWithoutHash = $data["password"];
    $password = trim(hash("sha256", $data["password"]));
    $permissionId = intval($data["permissionId"]);
    $adminId = intval($data["admin_id"]);

    // Генерация уникального access_token
    $access_token = md5($email . $number . $password . date("ymd"));

    // Подготовка SQL-запроса
    $query = $db->prepare(
        "INSERT INTO `users` (`user_name`, `user_surname`, `user_middlename`, `user_email`, `user_number`, `user_password`, `permission_id`, `user_access_token`, `user_admin_id`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    // Привязка параметров
    $name = trim($data["name"]);
    $surname = trim($data["surname"]);
    $middlename = trim($data["middlename"]);
    $query->bind_param(
        "ssssisisi",
        $name,
        $surname,
        $middlename,
        $email,
        $number,
        $password,
        $permissionId,
        $access_token,
        $adminId
    );

    // Выполнение запроса
    $query->execute();

    // Отправка сообщения на WhatsApp
    post([
        $number,
        "Здравствуйте, " .
        $name .
        '!

Сообщаем, что ваш номер был зарегистрирован для автосекретаря.

Ваши данные для входа:
Логин: ' .
        $number .
        '
Пароль: ' .
        $passwordWithoutHash .
        '
Ссылка для входа: https://schedule.choices.kz

С уважением,
КГУ "Центр информационных технологий"
Управление цифровизации и архивов Восточно-Казахстанской области',
    ]);
}

?>
