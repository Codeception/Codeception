<?php
namespace Codeception\Util;

if (PHP_VERSION_ID < 70000) {
    require_once 'ReflectionTestClass56.php';
} else {
    require_once 'ReflectionTestClass70.php';
}