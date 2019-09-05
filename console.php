#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Console\InitializeAccessToken;
use Console\UtdkInit;
use Symfony\Component\Console\Application;

$app = new Application('Console App', 'v1.0.0');
// $app->add(new UtdkInit());
$app->add(new InitializeAccessToken());
$app->add(new UtdkInit());
$app->run();