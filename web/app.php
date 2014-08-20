<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';

$apcLoader = new ApcClassLoader(sha1('Jira-Vote'), $loader);
$loader->unregister();
$apcLoader->register(true);

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();

$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
