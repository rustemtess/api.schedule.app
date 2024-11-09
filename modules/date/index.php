<?php

class Date implements Model {

    private array $absolutePath;

    /**
     * Устанавливает абсолютный путь для модуля.
     * 
     * @param array $absolutePath Массив с частями пути.
     * @return void
     */
    public function setPath(array $absolutePath): void {
        $this->absolutePath = $absolutePath;
    }

    /**
     * Выполняет действие на основе запроса.
     * 
     * Обрабатывает запросы на создание, получение списка и поиск дат, 
     * а также управляет цветами и временем.
     * 
     * @return void
     * @throws Exception
     */
    public function execute(): void {
        // Подключаем необходимые модули
        $this->includeModules();

        // Проверяем наличие access_token
        try {
            if (!isset($_POST['access_token'])) {
                http_response_code(404);
                throw new Exception('Токен доступа не найден');
            }
            $accessToken = $_POST['access_token'];
            getUser($accessToken);  // Проверка пользователя по access_token
        } catch (Exception $e) {
            json([
                'error' => $e->getMessage()
            ], 404);
        }

        // Обрабатываем запрос в зависимости от метода в URL
        switch ($this->absolutePath[0]) {
            case 'create':
                $this->handleCreate();
                break;

            case 'getlist':
                json(getOrSearchDateTimeList());
                break;

            case 'search':
                $this->handleSearch();
                break;

            case 'color':
                $this->handleColor();
                break;

            case 'time':
                $this->handleTime();
                break;

            default:
                throw new Exception('Неверный запрос в модуле');
        }
    }

    /**
     * Подключает все необходимые модули.
     * 
     * @return void
     */
    private function includeModules(): void {
        include_once 'add.module.php';
        include_once 'check.module.php';  
        include_once 'get.module.php';
        include_once 'delete.module.php';
        include_once __DIR__.'/../users/get.module.php';
    }

    /**
     * Обрабатывает запрос на создание новой записи.
     * 
     * @return void
     * @throws Exception
     */
    private function handleCreate(): void {
        // Проверяем наличие всех необходимых данных
        $requiredFields = ['date', 'time', 'text', 'access_token', 'colorId'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Поле $field не найдено");
            }
        }

        createDate(
            $_POST['date'], 
            $_POST['time'], 
            [
                $_POST['text'], 
                isset($_FILES['file']) ? $_FILES['file'] : [],
                $_POST['access_token'], 
                $_POST['colorId'], 
                isset($_POST['ids']) ? $_POST['ids'] : 'all'
            ]
        );
    }

    /**
     * Обрабатывает запрос на поиск.
     * 
     * @return void
     * @throws Exception
     */
    private function handleSearch(): void {
        if (!isset($_POST['text'])) {
            throw new Exception('Ошибка при поиске текста');
        }
        json(getOrSearchDateTimeList($_POST['text']));
    }

    /**
     * Обрабатывает запросы, связанные с цветами.
     * 
     * @return void
     * @throws Exception
     */
    private function handleColor(): void {
        switch ($this->absolutePath[1] ?? '') {
            case 'getlist':
                json(getColors());
                break;

            default:
                throw new Exception('Ошибка в модуле для работы с цветами');
        }
    }

    /**
     * Обрабатывает запросы, связанные с временем.
     * 
     * @return void
     * @throws Exception
     */
    private function handleTime(): void {
        switch ($this->absolutePath[1] ?? '') {
            case 'add':
                if (!isset($_POST['hours'])) {
                    throw new Exception('Часы не найдены');
                }
                createTime($_POST['hours']);
                break;

            case 'delete':
                if (!isset($_POST['time_id']) || !isset($_POST['user_id'])) {
                    throw new Exception('ID времени или пользователя не найдено');
                }
                deleteTimeById(intval($_POST['time_id']), intval($_POST['user_id']));
                break;

            case 'info':
                if (!isset($_POST['time_id'])) {
                    throw new Exception('ID времени не найдено');
                }
                json(getTimeInfo(intval($_POST['time_id'])));
                break;

            case 'getlist':
                json(getTimeList());
                break;

            default:
                throw new Exception('Ошибка в модуле для работы с временем');
        }
    }
}

?>
