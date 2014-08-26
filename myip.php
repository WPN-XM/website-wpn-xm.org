<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2014 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

$ip = $_SERVER['HTTP_X_REAL_IP'];

if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) === 1) {
    echo $ip;
} else {
    echo '0.0.0.0';
}
