<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium\Specification\Dumper;

use Selenium\Specification\Specification;
use Selenium\Specification\Method;
use Selenium\Specification\Dumper\MethodBuilder;

/**
 * Dumps the Selenium specification in a class file
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class BrowserDumper
{
    /**
     * Specification of the client
     *
     * @var Selenium\Specification\Specification
     */
    protected $specification;

    /**
     * Instanciates the dumper
     *
     * @param Selenium\Specification\Specification $specification The
     * specification to dump
     */
    public function __construct(Specification $specification)
    {
        $this->specification = $specification;
    }

    /**
     * Dumps to source code
     *
     * @return string The sourcecode
     */
    public function dump()
    {
        $methods = $this->specification->getMethods();

        $result = '<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium;

/**
 * Browser class containing all methods of Selenium Server, with documentation.
 *
 * This class was generated, do not modify it.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Browser extends BaseBrowser
{
';

        foreach ($methods as $method)
        {
            $result .= $this->dumpMethod($method)."\n\n";
        }

        $result .= "}
";

        return $result;
    }

    /**
     * Dumps a method.
     *
     * @param Selenium\Specification\Method $method Specification of a method
     */
    protected function dumpMethod(Method $method)
    {
        $builder = new MethodBuilder();

        $documentation = $method->getDescription()."\n\n";
        $signature = array();

        foreach ($method->getParameters() as $parameter) {
            $builder->addParameter($parameter->getName());
            $documentation .= "@param string $".$parameter->getName()." ".$parameter->getDescription()."\n\n";
            $signature[] = '$'.$parameter->getName();
        }

        $signature = implode(', ', $signature);

        if ($method->isAction()) {
            $documentation .= '@return Selenium\Browser Fluid interface';

            $body  = '$this->driver->action("'.$method->getName().'"'. ($signature ? ', '.$signature : '') . ');'."\n";
            $body .= "\n";
            $body .= "return \$this;";

        } else {
            $returnType = $method->getReturnType();
            $documentation .= '@return '.$returnType.' '.$method->getReturnDescription();

            if ($returnType === 'boolean') {
                $getMethod = 'getBoolean';
            } elseif ($returnType === 'string') {
                $getMethod = 'getString';
            } elseif ($returnType === 'string[]') {
                $getMethod = 'getStringArray';
            } elseif ($returnType === 'number') {
                $getMethod = 'getNumber';
            }

            $body = 'return $this->driver->'.$getMethod.'("'.$method->getName().'"'.($signature ? ', '.$signature : '').');';
        }

        $builder->setName($method->getName());
        $builder->setBody($body);
        $builder->setDocumentation($documentation);

        return $builder->buildCode();
    }
}
