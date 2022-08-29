<?php

declare(strict_types=1);

namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function class_exists;
use function is_array;
use function key;
use function reset;

class ExtensionLoader implements EventSubscriberInterface
{
    use Shared\StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    protected static array $events = [
        Events::MODULE_INIT => 'registerSuiteExtensions',
        Events::SUITE_AFTER => 'stopSuiteExtensions'
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * @var array<class-string, EventSubscriberInterface>
     */
    protected array $globalExtensions = [];

    /**
     * @var array<class-string, EventSubscriberInterface>
     */
    protected array $suiteExtensions = [];

    public function __construct(protected EventDispatcher $dispatcher)
    {
        $this->config = Configuration::config();
    }

    /**
     * @param array<string, mixed> $options
     * @throws ConfigurationException
     */
    public function bootGlobalExtensions(array $options): void
    {
        $this->options = $options;
        $this->globalExtensions = $this->bootExtensions($this->config);
    }

    public function registerGlobalExtensions(): void
    {
        foreach ($this->globalExtensions as $extension) {
            $this->dispatcher->addSubscriber($extension);
        }
    }

    public function registerSuiteExtensions(SuiteEvent $event): void
    {
        $suiteConfig = $event->getSettings();
        $extensions = $this->bootExtensions($suiteConfig);

        $this->suiteExtensions = [];
        foreach ($extensions as $extension) {
            $extensionClass = $extension::class;
            if (isset($this->globalExtensions[$extensionClass])) {
                continue; // already globally enabled
            }
            $this->dispatcher->addSubscriber($extension);
            $this->suiteExtensions[$extensionClass] = $extension;
        }
    }

    public function stopSuiteExtensions(): void
    {
        foreach ($this->suiteExtensions as $extension) {
            $this->dispatcher->removeSubscriber($extension);
        }
        $this->suiteExtensions = [];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<class-string, EventSubscriberInterface>
     * @throws ConfigurationException
     */
    protected function bootExtensions(array $config): array
    {
        $extensions = [];

        foreach ($config['extensions']['enabled'] as $extensionClass) {
            if (is_array($extensionClass)) {
                $extensionClass = key($extensionClass);
            }
            if (!class_exists($extensionClass)) {
                throw new ConfigurationException(
                    "Class `{$extensionClass}` is not defined. Autoload it or include into "
                    . "'_bootstrap.php' file of 'tests' directory"
                );
            }
            $extensionConfig = $this->getExtensionConfig($extensionClass, $config);

            $extension = new $extensionClass($extensionConfig, $this->options);
            if (!$extension instanceof EventSubscriberInterface) {
                throw new ConfigurationException(
                    "Class {$extensionClass} is not an EventListener. Please create it as Extension or GroupObject."
                );
            }
            $extensions[$extension::class] = $extension;
        }
        return $extensions;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function getExtensionConfig(string $extension, array $config): array
    {
        $extensionConfig = $config['extensions']['config'][$extension] ?? [];

        if (!isset($config['extensions']['enabled'])) {
            return $extensionConfig;
        }

        if (!is_array($config['extensions']['enabled'])) {
            return $extensionConfig;
        }

        foreach ($config['extensions']['enabled'] as $enabledExtensionsConfig) {
            if (!is_array($enabledExtensionsConfig)) {
                continue;
            }

            $enabledExtension = key($enabledExtensionsConfig);
            if ($enabledExtension === $extension) {
                $enabledExtensionConfig = reset($enabledExtensionsConfig);
                if (!is_array($enabledExtensionConfig)) {
                    return $extensionConfig;
                }
                return Configuration::mergeConfigs($enabledExtensionConfig, $extensionConfig);
            }
        }

        return $extensionConfig;
    }
}
