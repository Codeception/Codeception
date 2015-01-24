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
    '/cookies' => 'cookies',
    '/cookies2' => 'cookiesHeader',
    '/search.*' => 'search',
    '/login' => 'login',
    '/redirect' => 'redirect',
    '/redirect2' => 'redirect2',
    '/redirect3' => 'redirect3',
    '/redirect4' => 'redirect4',
    '/redirect_interval' => 'redirect_interval',
    '/redirect_header_interval' => 'redirect_header_interval',
    '/redirect_self' => 'redirect_self',
    '/facebook\??.*' => 'facebookController',
    '/form/(.*?)(#|\?.*?)?' => 'form',
    '/articles\??.*' => 'articles',
    '/auth' => 'httpAuth',
    '/register' => 'register'
);

glue::stick($urls);
