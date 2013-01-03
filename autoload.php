<?php

require_once __DIR__ .'/vendor/UniversalClassLoader.php';

if (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
    $namespaceMap = require_once __DIR__ . '/vendor/composer/autoload_namespaces.php';
    $classesMap = require_once __DIR__ . '/vendor/composer/autoload_classmap.php';

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(
        array(
            'Codeception' => __DIR__ . '/src'
        )
    );
    $loader->registerNamespaces($namespaceMap);
    $loader->register(true);

    spl_autoload_register(
        function ($class) use ($classesMap) {
            if (isset($classesMap[$class])) {
                require $classesMap[$class];
            }
        },
        true
    );

} elseif (stream_resolve_include_path(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

