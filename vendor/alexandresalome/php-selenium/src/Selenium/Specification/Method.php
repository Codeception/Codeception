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
 * Representation of a method of the Selenium server
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Method
{
    const TYPE_ACCESSOR = 'accessor';
    const TYPE_ACTION   = 'action';

    /**
     * Name of the method
     *
     * @var string
     */
    protected $name;

    /**
     * Description or documentation of the method
     *
     * @var string
     */
    protected $description;

    /**
     * Type of the method (action or accessor)
     *
     * @var string
     *
     * @see self::TYPE_*
     */
    protected $type;

    /**
     * Parameters of the method
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * Return type of the method
     *
     * @var string
     */
    protected $returnType;

    /**
     * Description of the return value
     *
     * @var string
     */
    protected $returnDescription;

    /**
     * Instanciates the method.
     *
     * @param string $name Name of the method
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name of the method.
     *
     * @return string The name of the method
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds a parameter to the method.
     *
     * @param Selenium\Specification\Parameter $parameter Parameter to add
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * Defines the description of the method.
     *
     * @param string $description Description of the method
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Defines the type of method (action or accessor)
     *
     * @param string $type Type of the method
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the type of method (action or accessor)
     *
     * @return string Type of the method
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Tests if the method is an action.
     *
     * @return boolean Result of the test
     */
    public function isAction()
    {
        return $this->type === self::TYPE_ACTION;
    }

    /**
     * Tests if the method is an accessor.
     *
     * @return boolean Result of the test
     */
    public function isAccessor()
    {
        return $this->type === self::TYPE_ACCESSOR;
    }

    /**
     * Defines the return value type of the method.
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * Defines the return value description of the method.
     */
    public function setReturnDescription($returnDescription)
    {
        $this->returnDescription = $returnDescription;
    }

    /**
     * Returns parameters of the method.
     *
     * @return array An array of parameter objects
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the description of the method.
     *
     * @return string The description text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the type of return value.
     *
     * @return string The return type
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Returns the description of return value.
     *
     * @return string The description or return value
     */
    public function getReturnDescription()
    {
        return $this->returnDescription;
    }
}
