<?php

/**
 * Загружает файл на сервер.
 * 
 * Этот метод обрабатывает загрузку файла, генерирует уникальное имя
 * для сохранения файла и перемещает файл в папку на сервере.
 * 
 * @param array $file Массив, содержащий информацию о файле, загруженном через форму.
 * @return string Возвращает уникальное имя загруженного файла с расширением.
 * @throws Exception Если файл не прошел проверку или произошла ошибка при загрузке.
 */
function uploadFile(array $file): string {
    // Проверяем, существует ли файл в массиве и не произошла ли ошибка при загрузке
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Ошибка при загрузке файла.');
    }

    // Генерируем уникальное имя для файла (например, хешируем его с использованием даты)
    $fileName = md5($file['name'] . date('Y-m-d H:i:s'));

    // Получаем расширение файла
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

    // Указываем путь для сохранения файла
    $uploadDir = __DIR__ . "/../files/";
    $filePath = $uploadDir . $fileName . '.' . $fileExtension;

    // Перемещаем загруженный файл в нужную папку
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Не удалось загрузить файл на сервер.');
    }

    // Возвращаем имя файла с расширением
    return $fileName . '.' . $fileExtension;
}
?>
