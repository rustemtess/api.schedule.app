<?php

class Users implements Model {

    private array $absolutePath;

    public function setPath(array $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

    public function execute(): void
    {
        include_once 'get.module.php';
        include_once 'check.module.php';
        include_once 'add.module.php';
        include_once 'update.module.php';
        include_once 'delete.module.php';
        switch($this->absolutePath[0]) {
            case 'create':
                if(!isset($_POST['name']) || !isset($_POST['surname']) || !isset($_POST['middlename'])) throw new Exception('ФИО не полное');
                if(!isset($_POST['email']) || !isset($_POST['number']) || !isset($_POST['password'])) throw new Exception('Почта|Номер|Пароль не найден');
                if(!isset($_POST['permissionId']) || !isset($_POST['admin_id'])) throw new Exception('Права доступа не указана');
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
                break;
            case 'login':
                if(!isset($_POST['login']) || !isset($_POST['password'])) throw new Exception('Логин или пароль не заполнен');
                json([
                    'access_token' => authentication($_POST['login'], $_POST['password'])
                ]);
                break;
            case 'get':
                if(!isset($_POST['access_token'])) throw new Exception('Вы не передали токен');
                json(
                    getUser($_POST['access_token'])
                );
                break;
            case 'delete':
                if(!isset($_POST['user_id'])) throw new Exception('ID не передан');
                deleteUserById($_POST['user_id']);
                break;
            case 'update':
                if(!isset($_POST['user_id']) || !isset($_POST['permission_id'])) throw new Exception('Не все аргументы переданы');
                updatePermissionByUserId(intval($_POST['user_id']), intval($_POST['permission_id']));
                break;
            case 'search':
                if(!isset($_POST['fullname'])) throw new Exception('Ошибка при поиске');
                json(searchFullName($_POST['fullname']));
                break;
            case 'permissions':
                if(!isset($_POST['limit'])) throw new Exception('Не задан лимит');
                json(getPermissions(intval($_POST['limit'])));
                break;
            case 'getlist':
                if(!isset($_POST['admin_id']) || !isset($_POST['limit']) || !isset($_POST['offset']) || !isset($_POST['isSuperAdmin'])) throw new Exception('ID админа или лимит не задан');
                json(getUsers(intval($_POST['admin_id']), intval($_POST['limit']), intval($_POST['offset']), $_POST['isSuperAdmin']));
                break;
            default: 
                throw new Exception('Ошибка в модуле');
        }
    }

}

?>