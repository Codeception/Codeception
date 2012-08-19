<?php

include_once 'server.php';

$GLOBALS['RESTmap'] = array();

$GLOBALS['RESTmap']['GET'] = array('USER' => function() {
    return array(
        'name'    => 'davert',
        'email'   => 'davert@mail.ua',
        'aliases' => array(
            'DavertMik',
            'davert.ua'
        ),
        'address' => array(
            'city'    => 'Kyiv',
            'country' => 'Ukraine',
        ),
    );
});

$GLOBALS['RESTmap']['POST'] = array('USER' => function() {
    $name = $_POST['name'];
    return array('name' => $name);
});

$GLOBALS['RESTmap']['PUT'] = array('USER' => function() {
    $name = $_REQUEST['name'];
    $user = array('name' => 'davert', 'email' => 'davert@mail.ua');;
    $user['name'] = $name;
    return $user;
});

$GLOBALS['RESTmap']['DELETE'] = array('USER' => function() {
    header('error', false, 404);
});

RESTServer();