<?php

/**
 * Получить список пользователей
 * @param int $adminId
 * @param int $limit
 * @param int $offset
 * @param string $isSuperAdmin
 * @return array
 */
function getUsers(int $adminId, int $limit = 10, int $offset = 0, string $isSuperAdmin = '0'): array {
    global $db;

    // Убедимся, что значения корректные
    $limit = max(0, $limit);
    $offset = max(0, $offset);

    $queryStr = "SELECT `user_id` as id, `user_name` as name,
                 `user_surname` as surname, `user_middlename` as middlename,
                 `user_email` as email, `user_number` as number,
                 `permission_id` as permissionId FROM `users`";
    
    if ($isSuperAdmin !== '1') {
        // Добавляем фильтрацию для пользователей, если не супер-админ
        $queryStr .= " WHERE `user_admin_id` = ?";
    }

    $queryStr .= " LIMIT ? OFFSET ?";

    $query = $db->prepare($queryStr);

    if ($isSuperAdmin !== '1') {
        $query->bind_param('iii', $adminId, $limit, $offset);
    } else {
        $query->bind_param('ii', $limit, $offset);
    }

    $query->execute();
    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Аутентификация пользователя
 * @param string $login
 * @param string $password
 * @return ?string - access_token
 */
function authentication(string $login, string $password): ?string {
    global $db;

    if (empty(trim($login)) || empty(trim($password))) {
        throw new Exception('Вы забыли заполнить все поля');
    }

    if (!isExistsNumber(intval($login))) {
        throw new Exception('Телефон номер не зарегистрирован');
    }

    $passwordHash = hash('sha256', $password);
    $query = $db->prepare(
        "SELECT `user_id`, `user_access_token` as access_token FROM `users` 
         WHERE `user_number` = ? AND `user_password` = ?"
    );

    $query->bind_param('ss', $login, $passwordHash);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    if (!$result || !isset($result['access_token'])) {
        throw new Exception('Неправильный логин или пароль');
    }

    $user_id = intval($result['user_id']);
    $access_token = md5($result['access_token'] . date('ymd'));

    $updateQuery = $db->prepare("UPDATE `users` SET `user_access_token` = ? WHERE `user_id` = ?");
    $updateQuery->bind_param('si', $access_token, $user_id);
    $updateQuery->execute();

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
        "SELECT `user_id` as id, `user_name` as name,
        `user_surname` as surname, `user_middlename` as middlename,
        `user_number` as number, `user_email` as email,
        users.permission_id as permissionId, `permission_name` as permissionName
        FROM `users`, `permissions` 
        WHERE `user_access_token` = ? AND permissions.permission_id = users.permission_id"
    );

    $query->bind_param('s', $access_token);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    if (!$result) {
        throw new Exception('Неправильный токен');
    }

    return $result;
}

/**
 * Получить список прав
 * @param int $limit
 * @return array
 */
function getPermissions(int $limit): array {
    global $db;

    $query = $db->prepare("SELECT `permission_id` as id, `permission_name` as name FROM `permissions` LIMIT ?");
    $query->bind_param('i', $limit);
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
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
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Получить список пользователей
 * @return array
 */
function getUserList(): array {
    global $db;

    $query = $db->prepare("SELECT user_id as id, user_name as name, user_surname as surname, user_middlename as middlename FROM users");
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>
