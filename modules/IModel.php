<?php

interface Model {
    /**
     * Установить абсолютный путь
     * @param array $absolutePath
     * @return void
     */
    public function setPath(array $absolutePath): void;
    /**
     * Запустить модуль
     * @return void
     */
    public function execute(): void;
}

?>