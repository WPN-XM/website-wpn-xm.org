<?php
    $ip = $_SERVER['HTTP_X_REAL_IP']; # $_SERVER['REMOTE_ADDR'];
    if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) == 1) {
        echo $ip;
    }
    else {
        echo '0.0.0.0';
    }
?>