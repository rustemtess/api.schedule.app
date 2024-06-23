<?php

class WhatsApp implements Model {

    private array $absolutePath;

    public function setPath(array $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

    public function execute(): void
    {
        require_once 'config/api.php';
        require_once 'module/request.php';
        require_once 'account/sendMessage.php';
        require_once 'account/types.php';
        require_once 'get.module.php';
        switch($this->absolutePath[0]) {
            case 'notification':
                $meeting = getMeetNext30Min();
                if(count($meeting) > 0) {
                    $numbers = getNumbers();
                    foreach($numbers as $number) {
                        $meetTime = $meeting[0]['time'];
                        $meetText = $meeting[0]['text'];
                        $userNumber = $number['user_number'];
                        if(strlen($userNumber) != 11) $userNumber = '7'.$userNumber;
                        if(strlen($userNumber) == 11) Account\sendMessage(
                            intval($number['user_number']), 
                            "Сообщаем вам, что намечается встреча '$meetText' на сегодня в $meetTime.", 
                            Account\Type::C_US
                        );
                    }
                }
                break;
            default: 
                throw new Exception('Ошибка в модуле');
        }
    }

}


?>