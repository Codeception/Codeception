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
    protected int|string|null $index = null;

    protected array $params = [
        'env'        => [],
        'group'      => [],
        'depends'    => [],
        'skip'       => null,
        'incomplete' => null,
    ];

    protected array $current  = [];
    protected array $services = [];
    protected array $reports  = [];

    /** @var string[] */
    private array $beforeClassMethods = [];

    /** @var string[] */
    private array $afterClassMethods  = [];

    public function getEnv(): array
    {
        return $this->params['env'];
    }

    public function getGroups(): array
    {
        return array_unique($this->params['group']);
    }

    /** @param string[] $groups */
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

    public function getCurrent(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->current;
        }
        return $this->current[$key] ?? ($key === 'name' ? $this->getName() : null);
    }

    public function setCurrent(array $currents): void
    {
        $this->current = array_merge($this->current, $currents);
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFilename(): string
    {
        return $this->filename ?: '';
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function setIndex(int|string $index): void
    {
        $this->index = $index;
    }

    public function getIndex(): int|string|null
    {
        return $this->index;
    }

    /** @return string[] */
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
     */
    public function getParam(?string $key = null): mixed
    {
        return $key === null ? $this->params : ($this->params[$key] ?? null);
    }

    public function setParamsFromAnnotations($annotations): void
    {
        $this->params = array_merge_recursive(
            $this->params,
            Annotation::fetchAllAnnotationsFromDocblock((string) $annotations)
        );
        $this->setSingularValueForSomeParams();
    }

    private function setSingularValueForSomeParams(): void
    {
        foreach (['skip', 'incomplete'] as $single) {
            $value = $this->params[$single] ?? null;
            if (is_array($value)) {
                $this->params[$single] = $value[1] ?? $value[0] ?? '';
            }
        }
    }

    public function setParamsFromAttributes($attributes): void
    {
        $params = [];
        foreach ($attributes as $attribute) {
            $name      = lcfirst(str_replace('Codeception\\Attribute\\', '', (string) $attribute->getName()));
            $arguments = $attribute->getArguments();

            if ($attribute->isRepeated()) {
                $params[$name][] = $arguments;
            } else {
                $params[$name] = $arguments;
            }
        }

        $this->params = array_merge_recursive($this->params, $params);

        foreach (['group', 'env', 'before', 'after', 'prepare'] as $single) {
            if (isset($this->params[$single]) && is_array($this->params[$single])) {
                $this->params[$single] = array_merge(
                    ...array_map(static fn($a): array => (array) $a, $this->params[$single])
                );
            }
        }

        $this->setSingularValueForSomeParams();
    }

    /** @deprecated */
    public function setParams(array $params): void
    {
        $this->params = array_merge_recursive($this->params, $params);
    }

    /** @param string[] $beforeClassMethods */
    public function setBeforeClassMethods(array $beforeClassMethods): void
    {
        $this->beforeClassMethods = $beforeClassMethods;
    }

    /** @return string[] */
    public function getBeforeClassMethods(): array
    {
        return $this->beforeClassMethods;
    }

    /** @param string[] $afterClassMethods */
    public function setAfterClassMethods(array $afterClassMethods): void
    {
        $this->afterClassMethods = $afterClassMethods;
    }

    /** @return string[] */
    public function getAfterClassMethods(): array
    {
        return $this->afterClassMethods;
    }
}
