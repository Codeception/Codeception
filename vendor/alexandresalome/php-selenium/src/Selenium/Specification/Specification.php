<?php
/*
 * This file is part of PHP Selenium Library.
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Selenium\Specification;

/**
 * Representation of the Selenium specification
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Specification
{
    /**
     * Collection of specified methods
     *
     * @var array
     */
    protected $methods = array();

    /**
     * Adds a method to the specification.
     *
     * @param Selenium\Specification\Method Method to add
     */
    public function addMethod(Method $method)
    {
        $this->methods[] = $method;
    }

    /**
     * Returns all the methods in the specification.
     *
     * @return array An array of Selenium\Specification\Method objects
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
