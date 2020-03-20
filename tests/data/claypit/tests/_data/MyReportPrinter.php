<?php

$series = \PHPUnit\Runner\Version::series();
if ($series >= 9) {
    require __DIR__ . '/MyReportPrinter9.php';
} else if ($series >= 7) {
    require __DIR__ . '/MyReportPrinter7.php';
} else {
    require __DIR__ . '/MyReportPrinter5.php';
}
