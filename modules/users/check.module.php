<?php

/**
 * Проверка почты и номера телефона
 * @param string $email
 * @param int $number
 * @return bool
 */
function isExistsNumberOrEmail(string $email, int $number): bool {
    global $db;
    $number = intval($number);
    return ($db->query(
        "SELECT `user_id` FROM `users` 
        WHERE `user_email` = '$email' OR `user_number` = $number"
    )->fetch_assoc() !== null) ? true : false;
}

?>