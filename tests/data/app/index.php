<?php

    if (!headers_sent()) header('Content-Type: text/html; charset=UTF-8');

    require_once('glue.php');
    require_once('data.php');
    require_once('controllers.php');

    $urls = array(
        '/' => 'index',
        '/info' => 'info',
        '/login' => 'login',
        '/form/(field|select|checkbox|file|textarea|hidden|complex)(#)?' => 'form'
    );

    glue::stick($urls);
?>