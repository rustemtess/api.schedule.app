<?php

/**
 * Загрузить файл
 * @param array $file
 * @return string
 */
function uploadFile(array $file): string {
    $fileName = md5($file['name'].date('Y-m-d H:i:s')); // Генерируем имя файла (например, хешируем его)
    // Получаем расширение файла
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Переместить загруженный файл в папку files с сохранением его оригинального расширения
    move_uploaded_file($file['tmp_name'], "./files/$fileName.$fileExtension");
    return $fileName.'.'.$fileExtension;
}
