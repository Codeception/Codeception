<?php

require_once __DIR__ . '/../autoload.php';

function clean_doc($doc, $indent = 3)
{
    $lines = explode("\n", $doc);
    $lines = array_map(function ($line) use ($indent)
    {
        return substr($line, $indent);
    }, $lines);
    $doc = implode("\n", $lines);
    $doc = str_replace(array('@since'), array(' * available since version'), $doc);
    $doc = str_replace(array(' @', "\n@"), array("  * ", "\n * "), $doc);
    return $doc;
}

$modules = \Symfony\Component\Finder\Finder::create()->files('*.php')->in(__DIR__ . '/../src/Codeception/Module');

foreach ($modules as $module) {

    $moduleName = basename(substr($module, 0, -4));
    $text = '# ' . $moduleName . " Module\n";

    $text .= "**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/$moduleName.php)**\n\n";

    $className = '\Codeception\Module\\' . $moduleName;
    $class = new ReflectionClass($className);

    $doc = $class->getDocComment();
    if ($doc) $text .= clean_doc($doc, 3);
    $text .= "\n## Actions\n\n";

    $reference = array();
    foreach ($class->getMethods() as $method) {
        // if ($method->getDeclaringClass()->name != $className) continue;
        if ($method->isConstructor() or $method->isDestructor()) continue;
        if (strpos($method->name, '_') === 0) continue;
        if ($method->isPublic()) {
            $title = "\n### " . $method->name . "\n\n";
            $doc = $method->getDocComment();
            if (!$doc) {
                $interfaces = $class->getInterfaces();
                foreach ($interfaces as $interface) {
                    $i = new \ReflectionClass($interface->name);
                    if ($i->hasMethod($method->name)) {
                        $doc = $i->getMethod($method->name)->getDocComment();
                        break;
                    }
                }
            }

            if (!$doc) {
                $parent = new \ReflectionClass($class->getParentClass()->name);
                if ($parent->hasMethod($method->name)) {
                    $doc = $parent->getMethod($method->name)->getDocComment();
                }
            }
            $doc = $doc ? clean_doc($doc, 7) : "__not documented__\n";
            $reference[$method->name] = $title . $doc;
        }

    }
    ksort($reference);
    $text .= implode("\n", $reference);

    file_put_contents(__DIR__ . '/../docs/modules/' . $moduleName . '.md', $text);

}
