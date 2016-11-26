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
    '/redirect_params' => 'redirect_params',
    '/redirect_interval' => 'redirect_interval',
    '/redirect_header_interval' => 'redirect_header_interval',
    '/redirect_meta_refresh' => 'redirect_meta_refresh',
    '/location_201' => 'location_201',
    '/relative_redirect' => 'redirect_relative',
    '/relative/redirect' => 'redirect_relative',
    '/redirect_twice' => 'redirect_twice',
    '/relative/info' => 'info',
    '/somepath/redirect_base_uri_has_path' => 'redirect_base_uri_has_path',
    '/somepath/redirect_base_uri_has_path_302' => 'redirect_base_uri_has_path_302',
    '/somepath/info' => 'info',
    '/facebook\??.*' => 'facebookController',
    '/form/(.*?)(#|\?.*?)?' => 'form',
    '/articles\??.*' => 'articles',
    '/auth' => 'httpAuth',
    '/register' => 'register',
    '/content-iso' => 'contentType1',
    '/content-cp1251' => 'contentType2',
    '/unset-cookie' => 'unsetCookie',
    '/external_url' => 'external_url',
    '/iframe' => 'iframe',
    '/basehref' => 'basehref',
);

glue::stick($urls);
