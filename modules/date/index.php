<?php

class Date implements Model {

    private array $absolutePath;

    public function setPath(array $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

    public function execute(): void
    {
        include_once 'add.module.php';
        include_once 'check.module.php';  
        include_once 'get.module.php';
        include_once 'delete.module.php';
        switch($this->absolutePath[0]) {
            case 'create':
                if(!isset($_POST['date']) || !isset($_POST['time'])) throw new Exception('Дата|Время не найдена');
                if(!isset($_POST['text'])) throw new Exception('Текст не найдена');
                if(!isset($_POST['access_token']) || !isset($_POST['colorId'])) throw new Exception('ID цвет|Токен не найдена');
                createDate(
                    $_POST['date'], $_POST['time'], 
                    [
                        $_POST['text'], isset($_FILES['file']) ? $_FILES['file'] : [], $_POST['access_token'], $_POST['colorId']
                    ]
                );

                break;
            case 'getlist':
                json(getOrSearchDateTimeList());
                break;
            case 'search':
                if(!isset($_POST['text'])) throw new Exception('Ошибка при поиске текста');
                json(getOrSearchDateTimeList($_POST['text']));
                break;
            case 'color':
                switch($this->absolutePath[1]) {
                    case 'getlist':
                        json(getColors());
                        break;
                    default: throw new Exception('Ошибка в модуле');
                }
                break;
            case 'time':
                switch($this->absolutePath[1]) {
                    case 'add':
                        if(!isset($_POST['hours'])) throw new Exception('Время не найдена');
                        createTime($_POST['hours']);
                        break;
                    case 'delete':
                        if(!isset($_POST['time_id']) || !isset($_POST['user_id'])) throw new Exception('ID времени|пользователя не найден');
                        deleteTimeById(intval($_POST['time_id']), intval($_POST['user_id']));
                        break;
                    case 'info':
                        if(!isset($_POST['time_id'])) throw new Exception('ID времени не найден');
                        json(getTimeInfo(intval($_POST['time_id'])));
                        break;
                    case 'getlist':
                        json(getTimeList());
                        break;
                    default: throw new Exception('Ошибка в модуле');
                }
                break;
            default: 
                throw new Exception('Ошибка в модуле');
        }
    }

}

?>