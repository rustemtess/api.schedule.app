<?php

class WhatsApp implements Model {

    private array $absolutePath; // Массив для хранения пути запроса

    // Устанавливает путь запроса
    public function setPath(array $absolutePath): void
    {
        $this->absolutePath = $absolutePath;
    }

	// Подключаем все необходимые модули
    private function includeModules(): void
    {
        require_once 'config/api.php'; // Конфигурация API
        require_once 'module/request.php'; // Модуль для обработки запросов
        require_once 'account/sendMessage.php'; // Модуль для отправки сообщений
        require_once 'account/types.php'; // Типы сообщений
        require_once 'get.module.php'; // Модуль для получения данных
    }

    // Основной метод выполнения действий в зависимости от переданного пути
    public function execute(): void
    {
		global $db;

		$this->includeModules();

		// Проверяем, какой метод указан в запросе и выполняем соответствующее действие
        switch($this->absolutePath[0]) {
            case 'list':
				// Получаем список пользователей и их статусы для выбранной встречи
				$meetId = intval($_POST['meet_id']); // ID встречи
				$listResult = $db->query("SELECT user_number as number, meet_read as status FROM schedule_user_notifications WHERE meet_id = $meetId");
				$usersResult = $db->query("SELECT user_id, user_number, user_name, user_surname, user_middlename FROM users");
				$meetUsers = $db->query("SELECT meet_data.meet_users FROM meet_data WHERE meet_id = '$meetId'")->fetch_assoc();
				$meetUsers = ($meetUsers['meet_users'] === 'all') ? 'all' : json_decode($meetUsers['meet_users'], true);

				// Преобразуем результат первого запроса в массив для быстрого поиска
				$notifications = [];
				while ($row = $listResult->fetch_assoc()) {
					$notifications[$row['number']] = $row['status'];
				}

				// Преобразуем результат второго запроса в массив и объединяем данные
				$finalResults = [];
				while ($row = $usersResult->fetch_assoc()) {
					if (
						(is_array($meetUsers) and in_array($row['user_id'], $meetUsers)) ||
						(!is_array($meetUsers) and $meetUsers === 'all')
					) {
						$number = $row['user_number'];
						$status = isset($notifications[$number]) ? $notifications[$number] : '-1';

						$finalResults[] = [
							'name' => $row['user_name'],
							'surname' => $row['user_surname'],
							'middlename' => $row['user_middlename'],
							'number' => $number,
							'status' => $status
						];
					}
				}

				// Сортируем пользователей по статусу
				usort($finalResults, function($a, $b) {
					$statusOrder = ['1' => 1, '0' => 2, '-1' => 3]; // Порядок статусов
					return $statusOrder[$a['status']] - $statusOrder[$b['status']];
				});
				
				// Отправляем ответ в формате JSON
				die(json_encode($finalResults));
                break;

            case 'notification':
				// Получаем встречи, которые будут через 50 минут
                $meeting = getMeetNext50Min();
				
				// Если есть хотя бы одна встреча
                if (count($meeting) > 0) {
                    try {
                        // Получаем все номера пользователей
						$numbers = getNumbers();
						$queryNumbers = '';
						
						// Проходим по всем встречам
						for($i = 0; $i < count($meeting); $i++) {
                            $meet = $meeting[$i];
							$meetId = $meet['id'];
							$meetTime = $meet['time'];
							$meetText = $meet['text'];
							
							// Проходим по всем номерам
							foreach ($numbers as $number) {
								$userNumber = $number['user_number'];

								// Проверяем, должны ли мы отправлять сообщение всем или выбранным пользователям
								$meetUsers = ($meet['users'] === 'all') ? 'all' : json_decode($meet['users'], true);
								
								// Проверка на корректность номера
								if (strlen($userNumber) != 11)
									$userNumber = '7' . $userNumber;

								// Отправляем сообщение, если номер правильный
								if (strlen($userNumber) == 11) {
									if (
										(is_array($meetUsers) && in_array(strval($number['user_id']), $meetUsers)) ||
										(!is_array($meetUsers) && $meetUsers === 'all')
									) {
										// Отправляем сообщение через WhatsApp
										Account\sendMessage(
											intval($userNumber), 
											"Напоминаем вам, что намечается встреча '$meetText' на сегодня в $meetTime.", 
											Account\Type::C_US
										);
										$queryNumbers .= "('$userNumber', '$meetId'), ";
									}
								}
							}
						}
						
						// Сохраняем информацию о том, кому было отправлено сообщение
						$queryNumbers = rtrim($queryNumbers, ', ');
						$db->query("INSERT INTO `schedule_user_notifications`(`user_number`, `meet_id`) VALUES $queryNumbers");
                    } catch (Exception $e) {
                        // Логируем ошибку
                        error_log("Transaction failed: " . $e->getMessage());
                    }
                }
                break;

            case 'only':
				// Обрабатываем уведомления только для сегодняшних встреч
				try {
					$numbers = getNumbers();
					$queryNumbers = '';
					$meetId = $_POST['id'];
					$meetTime = $_POST['time'];
					$meetText = $_POST['text'];
					$meetUsersGet = $_POST['users'];
					
					// Проходим по номерам пользователей и отправляем уведомления
					foreach ($numbers as $number) {
						$userNumber = $number['user_number'];
						$meetUsers = ($meetUsersGet === 'all') ? 'all' : json_decode($meetUsersGet, true);

						if (strlen($userNumber) != 11)
							$userNumber = '7' . $userNumber;

						if (strlen($userNumber) == 11) {
							if (
								(is_array($meetUsers) && in_array(strval($number['user_id']), $meetUsers)) ||
								(!is_array($meetUsers) && $meetUsers === 'all')
							) {
								// Отправляем сообщение через WhatsApp
								Account\sendMessage(
									intval($userNumber), 
									"Сообщаем вам, о встрече '$meetText' на сегодня в $meetTime.", 
									Account\Type::C_US
								);
								$queryNumbers .= "('$userNumber', '$meetId'), ";
							}
						}
					}
					
					// Сохраняем информацию о том, кому было отправлено сообщение
					$queryNumbers = rtrim($queryNumbers, ', ');
					$db->query("INSERT INTO `schedule_user_notifications`(`user_number`, `meet_id`) VALUES $queryNumbers");
				} catch (Exception $e) {
					// Логируем ошибку
					error_log("Transaction failed: " . $e->getMessage());
				}
                break;

            case 'sendmsg':
				// Отправляем пользовательское сообщение через WhatsApp
				Account\sendMessage(
					intval($_POST['number']),
					$_POST['message'],
					Account\Type::C_US
				);
                break;

            default: 
                throw new Exception('Ошибка в модуле'); // Ошибка, если метод не найден
        }
    }
}

?>
