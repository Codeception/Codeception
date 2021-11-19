<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Exception\InjectionException;
use Codeception\Util\Annotation;
use function array_merge;
use function array_merge_recursive;
use function array_unique;

class Metadata
{
    protected ?string $name = null;

    protected ?string $filename = null;

    protected string $feature = '';

    protected ?int $index = null;

    protected array $params = [
        'env' => [],
        'group' => [],
        'depends' => [],
        'skip' => null,
        'incomplete' => null
    ];

    protected array $current = [];

    protected array $services = [];

    protected array $reports = [];

    public function getEnv(): array
    {
        return $this->params['env'];
    }

    public function getGroups(): array
    {
        return array_unique($this->params['group']);
    }

    /**
     * @param string[] $groups
     */
    public function setGroups(array $groups): void
    {
        $this->params['group'] = array_merge($this->params['group'], $groups);
    }

    public function getSkip(): ?string
    {
        return $this->params['skip'];
    }

    public function setSkip(string $skip): void
    {
        $this->params['skip'] = $skip;
    }

    public function getIncomplete(): ?string
    {
        return $this->params['incomplete'];
    }

    public function setIncomplete(string $incomplete): void
    {
        $this->params['incomplete'] = $incomplete;
    }

    /**
     * @return mixed
     */
    public function getCurrent(?string $key = null)
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

    public function setCurrent(array $currents): void
    {
        $this->current = array_merge($this->current, $currents);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setIndex(int $index): void
    {
        $this->index = $index;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getDependencies(): array
    {
        return $this->params['depends'];
    }

    public function isBlocked(): bool
    {
        return $this->getSkip() !== null || $this->getIncomplete() !== null;
    }

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function setFeature(string $feature): void
    {
        $this->feature = $feature;
    }

    public function getService(string $service): object
    {
        if (!isset($this->services[$service])) {
            throw new InjectionException("Service {$service} is not defined and can't be accessed from a test");
        }
        return $this->services[$service];
    }

    public function setServices(array $services): void
    {
        $this->services = $services;
    }

    /**
     * Returns all test reports
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    public function addReport(string $type, $report): void
    {
        $this->reports[$type] = $report;
    }

    /**
     * Returns test params like: env, group, skip, incomplete, etc.
     * Can return by annotation or return all if no key passed
     *
     * @return mixed
     */
    public function getParam(string $key = null)
    {
        if ($key) {
            if (isset($this->params[$key])) {
                return $this->params[$key];
            }
            return null;
        }

        return $this->params;
    }

    public function setParamsFromAnnotations($annotations): void
    {
        $params = Annotation::fetchAllAnnotationsFromDocblock((string)$annotations);
        $this->params = array_merge_recursive($this->params, $params);

        // set singular value for some params
        foreach (['skip', 'incomplete'] as $single) {
            $this->params[$single] = empty($this->params[$single]) ? null : (string) $this->params[$single][0];
        }
    }

    public function setParams(array $params): void
    {
        $this->params = array_merge_recursive($this->params, $params);
    }
}
