<?php

include_once './modules/users/get.module.php';

function createDate(string $date, string $time, array $data): void {
    global $db;
    if(count($data) !== 4) throw new Exception('Не достаточно данных');
    if(!isDate($date)) {
        $query = $db->prepare("INSERT INTO `date` (`date_ymd`) VALUES (?)");
        $query->bind_param('s', $date);
        $query->execute();
    }
    $dateId = getIdByDate($date);
    $timeId = getIdByTime($time);
    if($dateId && $timeId) {
        $query = $db->prepare(
            "INSERT INTO `meeting` 
            (`date_id`, `time_id`) VALUES 
            (?, ?)");
        $query->bind_param('ii', $dateId, $timeId);
        $query->execute();
        $meetId = $query->insert_id;
        $userId = getUser($data[2])['id'];
        $meetFile = (0 < count($data[1])) ? uploadFile($data[1]) : '';
        $query = $db->prepare(
            "INSERT INTO `meet_data` 
            (`meet_id`, `meet_data_time`, `meet_data_text`, `meet_data_file`, `color_id`, `user_id`) VALUES 
            (?, ?, ?, NULLIF(?, ''), ?, ?)");
        $query->bind_param('isssii', $meetId, $time, $data[0], $meetFile, $data[3], $userId);
        $query->execute();
    }
}

function createTime(string $time): void {
    global $db;
    if(!isTime($time)) {
        $query = $db->prepare("INSERT INTO `time` (`time_hours`) VALUES (?)");
        $query->bind_param('s', $date);
        $query->execute();
    }
}

?>