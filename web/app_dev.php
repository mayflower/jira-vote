<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$allowed_addresses = array('127.0.0.1', 'fe80::1', '::1', '192.168.56.1');

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $allowed_addresses))
    || !in_array(@$_SERVER['REMOTE_ADDR'], $allowed_addresses)
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
