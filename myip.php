<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2014 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * MyIP - echos the client's IP address.
 */
$ip = $_SERVER['HTTP_X_REAL_IP'];

echo (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) === 1) ? $ip : '0.0.0.0';
