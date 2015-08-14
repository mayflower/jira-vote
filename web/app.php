<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ClassLoader\ApcClassLoader;

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';
require_once __DIR__ . '/../app/AppCache.php';

if (extension_loaded('apc')) {
    $apcLoader = new ApcClassLoader(sha1('Jira-Vote'), $loader);
    $loader->unregister();
    $apcLoader->register(true);
}

$kernel = new AppKernel('prod', true);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
\Symfony\Component\Debug\Debug::enable();
Request::enableHttpMethodParameterOverride();
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
