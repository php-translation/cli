#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\ArgvInput;

set_time_limit(0);

require __DIR__.'/../vendor/autoload.php';

$configFile = getcwd().'/translation.yml';
if (!file_exists($configFile)) {
    echo sprintf('Warning: File "%s" was not found.'."\n\n", $configFile);
}

$input = new ArgvInput();
$kernel = new AppKernel('dev', false);
$application = new Application($kernel);
$application->run($input);
