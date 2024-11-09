<?php

require_once 'config.php';

/**
 * Получить URL API для выполнения запроса
 * Эта функция формирует URL для API на основе переданного метода, используя данные из конфигурационного файла.
 * 
 * @param string $method Метод, для которого нужно сформировать URL (например, "sendMessage", "getMessages" и т.д.)
 * @return string Полный URL, включающий параметры для доступа к API.
 */
function _url(string $method): string {
    // Формируем URL с использованием подстроки из константы INSTANCE и TOKEN из конфигурации
    // INSTANCE - это часть URL, которая зависит от окружения, например, "1" для первого сервера
    // TOKEN - это токен авторизации для доступа к API
    return 'https://'.substr(INSTANCE, 0, 4).'.api.greenapi.com/waInstance'.INSTANCE.'/'.$method.'/'.TOKEN;
}
?>
