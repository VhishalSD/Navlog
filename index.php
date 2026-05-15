<?php
require_once __DIR__ . '/classes/Autoloader.php';

Autoloader::register(__DIR__);
echo NavlogApplication::create()->render();