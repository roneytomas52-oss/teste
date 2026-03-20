<?php

declare(strict_types=1);

$runner = require dirname(__DIR__) . '/bootstrap/app.php';
$response = $runner();
$response->send();
