<?php
namespace Codeception\Subscriber;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExtensionLoader implements EventSubscriberInterface
{
    use Shared\StaticEvents;

    public static $events = [
        Events::MODULE_INIT => 'registerSuiteExtensions',
        Events::SUITE_AFTER  => 'stopSuiteExtensions'
    ];

    protected $config;
    protected $options = [];

    protected $globalExtensions = [];
    protected $suiteExtensions = [];

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->config = Configuration::config();
    }

    public function bootGlobalExtensions($options)
    {
        $this->options = $options;
        $this->globalExtensions = $this->bootExtensions($this->config);
    }

    public function registerGlobalExtensions()
    {
        foreach ($this->globalExtensions as $extension) {
            $this->dispatcher->addSubscriber($extension);
        }
    }

    public function registerSuiteExtensions(SuiteEvent $e)
    {
        $suiteConfig = $e->getSettings();
        $extensions = $this->bootExtensions($suiteConfig);

        $this->suiteExtensions = [];
        foreach ($extensions as $extension) {
            $extensionClass = get_class($extension);
            if (isset($this->globalExtensions[$extensionClass])) {
                continue; // already globally enabled
            }
            $this->dispatcher->addSubscriber($extension);
            $this->suiteExtensions[$extensionClass] = $extension;
        }
    }

    public function stopSuiteExtensions()
    {
        foreach ($this->suiteExtensions as $extension) {
            $this->dispatcher->removeSubscriber($extension);
        }
        $this->suiteExtensions = [];
    }

    protected function bootExtensions($config)
    {
        $extensions = [];

        foreach ($config['extensions']['enabled'] as $extensionClass) {
            if (is_array($extensionClass)) {
                $extensionClass = key($extensionClass);
            }
            if (!class_exists($extensionClass)) {
                throw new ConfigurationException(
                    "Class `$extensionClass` is not defined. Autoload it or include into "
                    . "'_bootstrap.php' file of 'tests' directory"
                );
            }
            $extensionConfig = $this->getExtensionConfig($extensionClass, $config);

            $extension = new $extensionClass($extensionConfig, $this->options);
            if (!$extension instanceof EventSubscriberInterface) {
                throw new ConfigurationException(
                    "Class $extensionClass is not an EventListener. Please create it as Extension or GroupObject."
                );
            }
            $extensions[get_class($extension)] = $extension;
        }
        return $extensions;
    }

    private function getExtensionConfig($extension, $config)
    {
        $extensionConfig = isset($config['extensions']['config'][$extension])
            ? $config['extensions']['config'][$extension]
            : [];

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
                return Configuration::mergeConfigs(reset($enabledExtensionsConfig), $extensionConfig);
            }
        }

        return $extensionConfig;
    }
}
