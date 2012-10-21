<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium\Specification\Dumper;

/**
 * Helper for building a PHP method
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class MethodBuilder
{
    /**
     * Documentation text of the method
     *
     * @var string
     */
    protected $documentation;

    /**
     * Name of the method
     *
     * @var string
     */
    protected $name;

    /**
     * Body of the method (contains calls, etc.)
     *
     * @var string
     */
    protected $body;

    /**
     * Array of parameters (string prefixed with '$')
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Sets the documentation block.
     *
     * @param string $documentation A documentation text
     *
     * @return Selenium\Specification\Dumper\MethodBuilder Fluid interface
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;

        return $this;
    }

    /**
     * Sets the name of the method.
     *
     * @param string $name A method name
     *
     * @return Selenium\Specification\Dumper\MethodBuilder Fluid interface
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the body of the method.
     *
     * @param string $body A body
     *
     * @return Selenium\Specification\Dumper\MethodBuilder Fluid interface
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Adds a parameter to the method.
     *
     * @param string $parameter A parameter name (without the '$')
     *
     * @return Selenium\Specification\Dumper\MethodBuilder Fluid interface
     */
    public function addParameter($parameter)
    {
        $this->parameters[] = '$'.$parameter;

        return $this;
    }

    /**
     * Builds the PHP code for the method.
     *
     * @return string The method code
     */
    public function buildCode()
    {
        $code = '';

        if ($this->documentation) {
            $code .= '    /**'."\n";
            $code .= '     * '.str_replace("\n", "\n     * ", wordwrap($this->documentation, 73))."\n";
            $code .= '     */'."\n";
        }

        $code .= '    public function '.$this->name.'('.implode(', ', $this->parameters).')'."\n";
        $code .= '    {'."\n";
        $code .= '        '.str_replace("\n", "\n        ", $this->body)."\n";
        $code .= '    }';

        return $code;
    }
}
