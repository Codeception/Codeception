<?php 

if (\PHPUnit\Runner\Version::series() < 7) {
    require __DIR__ . '/MyReportPrinter5.php';
} else if (\PHPUnit\Runner\Version::series() < 9) {
    require __DIR__ . '/MyReportPrinter7.php';
} else {
    require __DIR__ . '/MyReportPrinter9.php';
}
