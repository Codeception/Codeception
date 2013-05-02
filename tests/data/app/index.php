<?php
    if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');
    require_once __DIR__.'/../../../autoload.php';

    if (file_exists(__DIR__ . '/../sandbox/c3.php')) {
        require __DIR__ . '/../sandbox/c3.php';
    } else {
        require __DIR__ . '/../claypit/c3.php';
    }

    require_once('glue.php');
    require_once('data.php');
    require_once('controllers.php');

    $urls = array(
        '/' => 'index',
        '/info' => 'info',
        '/login' => 'login',
        '/form/(field|select|checkbox|file|textarea|hidden|complex|button|radio|empty|popup)(#)?' => 'form'
    );

    glue::stick($urls);
?>