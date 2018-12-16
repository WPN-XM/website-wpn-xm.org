<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * MyIP - echos the client's IP address.
 */

function getClientIP() 
{ 
    if (isset($_SERVER ['HTTP_X_FORWARDED_FOR'])) { 
        return $_SERVER ['HTTP_X_FORWARDED_FOR']; 
    }   
    if (isset($_SERVER ['HTTP_X_REAL_IP'])) { 
        return $_SERVER ['HTTP_X_REAL_IP']; 
    } 
    if(isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR']; 
    } 
    return false; 
}

$ip = getClientIP();

if($ip === false) {
    echo '0.0.0.0';
} elseif(preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) === 1) {
    echo $ip;
} else {
    echo '0.0.0.0';
}
