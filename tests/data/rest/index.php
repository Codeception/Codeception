<?php

include_once 'server.php';

$GLOBALS['RESTmap'] = array();

$GLOBALS['RESTmap']['GET'] = array('user' => function() {
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
        ));
    },
    'ping' => function() {
        $resp = "";
        foreach (getallheaders() as $k => $v) {
            $resp .= strtolower($k) . ": $v\n";
        }
        return $resp;
    }
);

$GLOBALS['RESTmap']['POST'] = array('user' => function() {
    $name = $_POST['name'];
    return array('name' => $name);
});

$GLOBALS['RESTmap']['PUT'] = array('user' => function() {
    $name = $_REQUEST['name'];
    $user = array('name' => 'davert', 'email' => 'davert@mail.ua');;
    $user['name'] = $name;
    return $user;
});

$GLOBALS['RESTmap']['DELETE'] = array('user' => function() {
    header('error', false, 404);
});

RESTServer();