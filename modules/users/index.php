<?php

class Users implements Model {

    private array $absolutePath;

    // Устанавливаем путь для обработки запросов
    public function setPath(array $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

    // Основная логика выполнения
    public function execute(): void
    {
        global $db;

        // Подключаем необходимые модули
        $this->includeModules();

        // Проверка access_token для защищенных запросов
        if ($this->isProtectedEndpoint() && !$this->isAuthenticated()) {
            http_response_code(401); // Не авторизован
            exit;
        }

        // Обработка запроса в зависимости от метода
        switch ($this->absolutePath[0]) {
            case 'create':
                $this->createUser();
                break;
            case 'login':
                $this->loginUser();
                break;
            case 'get':
                $this->getUserData();
                break;
            case 'delete':
                $this->deleteUser();
                break;
            case 'update':
                $this->updatePermissions();
                break;
            case 'search':
                $this->searchUsers();
                break;
            case 'permissions':
                $this->getPermissions();
                break;
            case 'getlist':
                $this->getUsersList();
                break;
            case 'list':
                $this->getAllUsers();
                break;
            default:
                $this->sendErrorResponse('Неверный запрос');
        }
    }

    // Подключаем все необходимые модули
    private function includeModules(): void
    {
        include_once 'get.module.php';
        include_once 'check.module.php';
        include_once 'add.module.php';
        include_once 'update.module.php';
        include_once 'delete.module.php';
    }

    // Проверка, требует ли метод аутентификацию
    private function isProtectedEndpoint(): bool
    {
        return $this->absolutePath[0] !== 'login';
    }

    // Проверка аутентификации пользователя
    private function isAuthenticated(): bool
    {
        if (!isset($_POST['access_token'])) {
            return false;
        }

        try {
            getUser($_POST['access_token']);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Регистрация нового пользователя
    private function createUser(): void
    {
        $requiredFields = ['name', 'surname', 'middlename', 'email', 'number', 'password', 'permissionId', 'admin_id'];
        $this->validateRequiredFields($requiredFields);

        register([
            'name' => $_POST['name'],
            'surname' => $_POST['surname'],
            'middlename' => $_POST['middlename'],
            'number' => $_POST['number'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'permissionId' => $_POST['permissionId'],
            'admin_id' => $_POST['admin_id']
        ]);
    }

    // Логин пользователя
    private function loginUser(): void
    {
        if (!isset($_POST['login']) || !isset($_POST['password'])) {
            $this->sendErrorResponse('Логин или пароль не заполнены');
        }

        $login = $this->normalizeLogin($_POST['login']);
        $accessToken = authentication($login, $_POST['password']);

        json(['access_token' => $accessToken]);
    }

    // Получение данных пользователя
    private function getUserData(): void
    {
        if (!isset($_POST['access_token'])) {
            $this->sendErrorResponse('Требуется access token');
        }

        json(getUser($_POST['access_token']));
    }

    // Удаление пользователя
    private function deleteUser(): void
    {
        if (!isset($_POST['user_id'])) {
            $this->sendErrorResponse('Не передан ID пользователя');
        }

        deleteUserById($_POST['user_id']);
    }

    // Обновление прав пользователя
    private function updatePermissions(): void
    {
        if (!isset($_POST['user_id']) || !isset($_POST['permission_id'])) {
            $this->sendErrorResponse('Не передан ID пользователя или ID прав');
        }

        updatePermissionByUserId(intval($_POST['user_id']), intval($_POST['permission_id']));
    }

    // Поиск пользователей по ФИО
    private function searchUsers(): void
    {
        if (!isset($_POST['fullname'])) {
            $this->sendErrorResponse('Не указано ФИО');
        }

        json(searchFullName($_POST['fullname']));
    }

    // Получение списка разрешений
    private function getPermissions(): void
    {
        if (!isset($_POST['limit'])) {
            $this->sendErrorResponse('Не указан лимит');
        }

        json(getPermissions(intval($_POST['limit'])));
    }

    // Получение списка пользователей
    private function getUsersList(): void
    {
        $requiredFields = ['admin_id', 'limit', 'offset', 'isSuperAdmin'];
        $this->validateRequiredFields($requiredFields);

        json(getUsers(intval($_POST['admin_id']), intval($_POST['limit']), intval($_POST['offset']), $_POST['isSuperAdmin']));
    }

    // Получение всех пользователей
    private function getAllUsers(): void
    {
        json(getUserList());
    }

    // Проверка и валидация обязательных полей
    private function validateRequiredFields(array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($_POST[$field])) {
                $this->sendErrorResponse("$field не передан");
            }
        }
    }

    // Нормализация логина (если начинается с '8', заменяем на '7')
    private function normalizeLogin(string $login): string
    {
        if (str_starts_with($login, '8') && strlen($login) == 11) {
            return preg_replace('/8/', '7', $login, 1);
        }
        return $login;
    }

    // Отправка ошибки с сообщением
    private function sendErrorResponse(string $message): void
    {
        json(['error' => $message], 200);
        exit;
    }
}

?>
