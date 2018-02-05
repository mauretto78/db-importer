<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

try {
    $config = Yaml::parse(file_get_contents(__DIR__.'/../config/parameters.yml'));
    return $config;
} catch (ParseException $e) {
    printf('Unable to parse the YAML string: %s', $e->getMessage());
}
