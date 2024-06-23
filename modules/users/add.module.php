<?php

/**
 * Регистрация пользователя
 * @param array $data
 * @return void
 */
function register(array $data): void {
    global $db;
    if(count($data) !== 8) throw new Exception('Не достаточно данных');
    $email = trim($data['email']);
    $number = intval($data['number']);
    if(strlen($number) !== 11) $number = str_replace('+', '', $number);
    if(isExistsNumberOrEmail($email, $number)) throw new Exception('E-mail или Номер уже зарегистрирован');
    $query = $db->prepare(
        "INSERT INTO `users` (`user_name`, `user_surname`, `user_middlename`, `user_email`, `user_number`, `user_password`, `permission_id`, `user_access_token`, `user_admin_id`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $name = trim($data['name']);
    $surname = trim($data['surname']);
    $middlename = trim($data['middlename']);
    $password = trim(hash('sha256', $data['password']));
    $permissionId = intval($data['permissionId']);
    $adminId = intval($data['admin_id']);
    $access_token = md5($email.$number.$password.date('ymd'));
    $query->bind_param('ssssisisi', $name, $surname, $middlename, $email, $number, $password, $permissionId, $access_token, $adminId);
    $query->execute();
}

?>