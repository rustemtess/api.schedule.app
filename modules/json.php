<?php

/**
 * Вывести в json формате
 * @param array $arr
 * @return void
 */
function json(array $arr): void {
    die(json_encode($arr));
}

?>