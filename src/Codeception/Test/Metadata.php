<?php
namespace Codeception\Test;

use Codeception\Exception\InjectionException;

class Metadata
{
    protected $name;
    protected $filename;
    protected $feature;

    protected $env = [];
    protected $groups = [];
    protected $dependencies = [];
    protected $skip = null;
    protected $incomplete = null;

    protected $current = [];
    protected $services = [];
    protected $reports = [];

    /**
     * @return mixed
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param mixed $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return array_unique($this->groups);
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = array_merge($this->groups, $groups);
    }

    /**
     * @return mixed
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * @param mixed $skip
     */
    public function setSkip($skip)
    {
        $this->skip = $skip;
    }

    /**
     * @return mixed
     */
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * @param mixed $incomplete
     */
    public function setIncomplete($incomplete)
    {
        $this->incomplete = $incomplete;
    }

    /**
     * @param null $key
     * @return array
     */
    public function getCurrent($key = null)
    {
        if ($key && isset($this->current[$key])) {
            return $this->current[$key];
        }
        if ($key) {
            return null;
        }
        return $this->current;
    }

    public function setCurrent(array $currents)
    {
        $this->current = array_merge($this->current, $currents);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function isBlocked()
    {
        return $this->skip !== null || $this->incomplete !== null;
    }

    /**
     * @return mixed
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @param mixed $feature
     */
    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    /**
     * @param $service
     * @return array
     * @throws InjectionException
     */
    public function getService($service)
    {
        if (!isset($this->services[$service])) {
            throw new InjectionException("Service $service is not defined and can't be accessed from a test");
        }
        return $this->services[$service];
    }

    /**
     * @param array $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return array
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param $type
     * @param $report
     */
    public function addReport($type, $report)
    {
        $this->reports[$type] = $report;
    }
}
