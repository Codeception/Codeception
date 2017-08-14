<?php
namespace Codeception\Test;

use Codeception\Exception\InjectionException;
use Codeception\Util\Annotation;

class Metadata
{
    protected $name;
    protected $filename;
    protected $feature;

    protected $params = [
        'env' => [],
        'group' => [],
        'depends' => [],
        'skip' => null,
        'incomplete' => null
    ];

    protected $current = [];
    protected $services = [];
    protected $reports = [];

    /**
     * @return mixed
     */
    public function getEnv()
    {
        return $this->params['env'];
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return array_unique($this->params['group']);
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->params['group'] = array_merge($this->params['group'], $groups);
    }

    /**
     * @return mixed
     */
    public function getSkip()
    {
        return $this->params['skip'];
    }

    /**
     * @param mixed $skip
     */
    public function setSkip($skip)
    {
        $this->params['skip'] = $skip;
    }

    /**
     * @return mixed
     */
    public function getIncomplete()
    {
        return $this->params['incomplete'];
    }

    /**
     * @param mixed $incomplete
     */
    public function setIncomplete($incomplete)
    {
        $this->params['incomplete'] = $incomplete;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getCurrent($key = null)
    {
        if ($key) {
            if (isset($this->current[$key])) {
                return $this->current[$key];
            }
            if ($key === 'name') {
                return $this->getName();
            }
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
        return $this->params['depends'];
    }

    public function isBlocked()
    {
        return $this->getSkip() !== null || $this->getIncomplete() !== null;
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
     * Returns all test reports
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

    /**
     * Returns test params like: env, group, skip, incomplete, etc
     * Can return by annotation or return all if no key passed
     *
     * @param null $key
     * @return array|mixed|null
     */
    public function getParam($key = null)
    {
        if ($key) {
            if (isset($this->params[$key])) {
                return $this->params[$key];
            }
            return null;
        }

        return $this->params;
    }

    /**
     * @param mixed $annotations
     */
    public function setParamsFromAnnotations($annotations)
    {
        $params = Annotation::fetchAllAnnotationsFromDocblock($annotations);
        $this->params = array_merge_recursive($this->params, $params);

        // set singular value for some params
        foreach (['skip', 'incomplete'] as $single) {
            $this->params[$single] = empty($this->params[$single]) ? null : (string) $this->params[$single][0];
        }
    }

    /**
     * @param $params
     */
    public function setParams($params)
    {
        $this->params = array_merge_recursive($this->params, $params);
    }
}
