<?php
namespace Codeception\Lib;

use Codeception\Exception\ConfigurationException;
use Symfony\Component\Yaml\Yaml;

class ParamsLoader
{
    protected $paramStorage;
    protected $paramsFile;

    public function load($paramStorage)
    {
        $this->paramsFile = null;
        $this->paramStorage = $paramStorage;

        if (is_array($paramStorage)) {
            return $this->loadArray();
        }

        if ($paramStorage === 'env' || $paramStorage === 'environment') {
            return $this->loadEnvironmentVars();
        }

        $this->paramsFile = codecept_absolute_path($paramStorage);
        if (!file_exists($this->paramsFile)) {
            throw new ConfigurationException("Params file {$this->paramsFile} not found");
        }

        try {
            if (preg_match('~\.ya?ml$~', $paramStorage)) {
                return $this->loadYamlFile();
            }

            if (preg_match('~\.ini$~', $paramStorage)) {
                return $this->loadIniFile();
            }

            if (preg_match('~\.php$~', $paramStorage)) {
                return $this->loadPhpFile();
            }

            if (preg_match('~(\.env(\.|$))~', $paramStorage)) {
                return $this->loadDotEnvFile();
            }

            if (preg_match('~\.xml$~', $paramStorage)) {
                return $this->loadXmlFile();
            }
        } catch (\Exception $e) {
            throw new ConfigurationException("Failed loading params from $paramStorage\n" . $e->getMessage());
        }

        throw new ConfigurationException("Params can't be loaded from `$paramStorage`.");
    }

    public function loadArray()
    {
        return $this->paramStorage;
    }

    protected function loadIniFile()
    {
        return parse_ini_file($this->paramsFile);
    }

    protected function loadPhpFile()
    {
        return require $this->paramsFile;
    }

    protected function loadYamlFile()
    {
        $params = Yaml::parse(file_get_contents($this->paramsFile));
        if (isset($params['parameters'])) { // Symfony style
            $params = $params['parameters'];
        }
        return $params;
    }

    protected function loadXmlFile()
    {
        $paramsToArray = function (\SimpleXMLElement $params) use (&$paramsToArray) {
            $a = [];
            foreach ($params as $param) {
                $key = isset($param['key']) ? (string) $param['key'] : $param->getName();
                $type = isset($param['type']) ? (string) $param['type'] : 'string';
                $value = (string) $param;
                switch ($type) {
                    case 'bool':
                    case 'boolean':
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                        $a[$key] = settype($value, $type);
                        break;
                    case 'constant':
                        $a[$key] = constant($value);
                        break;
                    case 'collection':
                        $a[$key] = $paramsToArray($param);
                        break;
                    default:
                        $a[$key] = (string) $param;
                }
            }

            return $a;
        };

        return $paramsToArray(simplexml_load_file($this->paramsFile));
    }

    protected function loadDotEnvFile()
    {
        if (class_exists('Dotenv\Dotenv')) {
            if (class_exists('Dotenv\Repository\RepositoryBuilder')) {
                //dotenv v4
                $repository = \Dotenv\Repository\RepositoryBuilder::create()
                    ->withReaders([new \Dotenv\Repository\Adapter\ServerConstAdapter()])
                    ->immutable()
                    ->make();
                $dotEnv = \Dotenv\Dotenv::create($repository, codecept_root_dir(), $this->paramStorage);
            } elseif (method_exists('Dotenv\Dotenv', 'create')) {
                //dotenv v3
                $dotEnv = \Dotenv\Dotenv::create(codecept_root_dir(), $this->paramStorage);
            } else {
                //dotenv v2
                $dotEnv = new \Dotenv\Dotenv(codecept_root_dir(), $this->paramStorage);
            }
            $dotEnv->load();
            return $_SERVER;
        } elseif (class_exists('Symfony\Component\Dotenv\Dotenv')) {
            $dotEnv = new \Symfony\Component\Dotenv\Dotenv();
            $dotEnv->load(codecept_root_dir($this->paramStorage));
            return $_SERVER;
        }

        throw new ConfigurationException(
            "`vlucas/phpdotenv` library is required to parse .env files.\n" .
            "Please install it via composer: composer require vlucas/phpdotenv"
        );
    }

    protected function loadEnvironmentVars()
    {
        return $_SERVER;
    }
}
