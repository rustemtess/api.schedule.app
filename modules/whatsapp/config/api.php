<?php

require_once 'config.php';

function _url(string $method): string {
    return 'https://'.substr(INSTANCE, 0, 4).'.api.greenapi.com/waInstance'.INSTANCE.'/'.$method.'/'.TOKEN;
}