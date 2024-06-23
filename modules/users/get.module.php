<?php

/**
 * Получить список пользователей
 * @return array
 */
function getUsers(int $adminId, int $limit = 10, int $offset = 0, string $isSuperAdmin = '0'): array {
    global $db;
    
    if($isSuperAdmin === '1') {
        return $db->query(
            "SELECT `user_id` as id, `user_name` as name, 
            `user_surname` as surname, `user_middlename` as middlename, 
            `user_email` as email, `user_number` as number, 
            `permission_id` as permissionId FROM `users` LIMIT $limit OFFSET $offset"
        )->fetch_all(MYSQLI_ASSOC);
    }

    return $db->query(
        "SELECT `user_id` as id, `user_name` as name, 
        `user_surname` as surname, `user_middlename` as middlename, 
        `user_email` as email, `user_number` as number, 
        `permission_id` as permissionId FROM `users` WHERE `user_admin_id` = $adminId LIMIT $limit OFFSET $offset"
    )->fetch_all(MYSQLI_ASSOC);
}


/**
 * Аутентификация пользователя
 * @param string $login Почта|Номер
 * @param string $password
 * @return string|null
 */
function authentication(string $login, string $password): ?string {
    global $db;
    if(empty(trim($login)) || empty(trim($password))) throw new Exception('Вы забыли заполнить');
    if(!isExistsNumberOrEmail($login, intval($login))) throw new Exception('Почта|Номер не зарегистрирован');
    $password = hash('sha256', $password);
    $query = $db->prepare(
        "SELECT `user_id`, `user_access_token` as access_token FROM `users` 
        WHERE (`user_email` = ? AND `user_password` = ?) OR (`user_number` = ? AND `user_password` = ?)"
        );
    $query->bind_param('ssss', $login, $password, $login, $password);
    $query->execute();
    $result = $query->get_result();
    $result = $result->fetch_assoc();
    if(!isset($result['access_token'])) throw new Exception('Неправильный логин или пароль');
    $user_id = intval($result['user_id']);
    $access_token = md5($result['access_token'].date('ymd'));
    $query = $db->prepare("UPDATE `users` SET `user_access_token` = ? WHERE `user_id` = ?");
    $query->bind_param('si', $access_token, $user_id);
    $query->execute();
    return $access_token;
}

/**
 * Получить данные пользователя по токену
 * @param string $access_token
 * @return array|null
 */
function getUser(string $access_token): ?array {
    global $db;
    $query = $db->prepare(
        "SELECT `user_id` as id,
        `user_name` as name,
        `user_surname` as surname,
        `user_middlename` as middlename,
        `user_number` as number,
        `user_email` as email,
        users.permission_id as permissionId,
        `permission_name` as permissionName 
        FROM `users`, `permissions` WHERE `user_access_token` = ? AND permissions.permission_id = users.permission_id"
    );
    $query->bind_param('s', $access_token);
    $query->execute();
    $result = $query->get_result();
    $result = $result->fetch_assoc();
    if(!$result) throw new Exception('Неправильный токен');
    return $result;
}

/**
 * Получить список прав
 * @return array
 */
function getPermissions(int $limit): array {
    global $db;
    return $db->query("SELECT `permission_id` as id, `permission_name` as name FROM permissions LIMIT $limit")->fetch_all(MYSQLI_ASSOC);
}

/**
 * Поиск ФИО
 * @param string $fullname
 * @return array
 */
function searchFullName(string $fullname): array {
    global $db;

    // Разделение строки fullname на отдельные слова
    $terms = explode(' ', $fullname);
    // Создание массива для параметров и частей запроса
    $queryParts = [];
    $queryParams = [];
    // Добавление условий для каждого термина
    foreach ($terms as $term) {
        $likeParam = '%' . $term . '%';
        $queryParts[] = '(user_name LIKE ? OR user_surname LIKE ? OR user_middlename LIKE ?)';
        $queryParams[] = $likeParam;
        $queryParams[] = $likeParam;
        $queryParams[] = $likeParam;
    }
    // Создание финального запроса
    $queryStr = "SELECT `user_id` as id, `user_name` as name, 
                        `user_surname` as surname, `user_middlename` as middlename, 
                        `user_email` as email, `user_number` as number, 
                        `permission_id` as permissionId 
                 FROM `users` 
                 WHERE " . implode(' AND ', $queryParts);

    // Подготовка запроса
    $query = $db->prepare($queryStr);
    // Динамическое привязывание параметров
    $types = str_repeat('s', count($queryParams));
    $query->bind_param($types, ...$queryParams);
    // Выполнение запроса
    $query->execute();
    // Получение результатов
    $result = $query->get_result();
    // Получение всех записей в виде ассоциативного массива
    $users = $result->fetch_all(MYSQLI_ASSOC);
    // Возврат результатов
    return $users;
}


?>