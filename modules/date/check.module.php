<?php

/**
 * @param string $date
 * @return bool
 */
function isDate(string $date): bool {
    global $db;
    return ($db->query("SELECT * FROM `date` WHERE date_ymd = '$date'")->fetch_assoc() !== null) ? true : false;
}

/**
 * @param string $time
 * @return bool
 */
function isTime(string $time): bool {
    global $db;
    return empty($db->query("SELECT * FROM `time` WHERE time_hours = $time")->fetch_assoc());
}

?>