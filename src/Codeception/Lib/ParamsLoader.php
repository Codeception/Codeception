<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Exception\ConfigurationException;
use Dotenv\Dotenv as PhpDotenv;
use Dotenv\Repository\RepositoryBuilder;
use Exception;
use SimpleXMLElement;
use Symfony\Component\Dotenv\Dotenv as SymfonyDotenv;
use Symfony\Component\Yaml\Yaml;

use function class_exists;
use function codecept_absolute_path;
use function codecept_relative_path;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function method_exists;
use function parse_ini_file;
use function preg_match;

class ParamsLoader
{
    /**
     * @param array<mixed>|string $paramStorage
     * @return array<mixed>
     * @throws ConfigurationException
     */
    public function load(array|string $paramStorage): array
    {
        if (is_array($paramStorage)) {
            return $paramStorage;
        }

        if ($paramStorage === 'env' || $paramStorage === 'environment') {
            return $this->loadEnvironmentVars();
        }

        $paramsFile = codecept_absolute_path($paramStorage);
        if (!file_exists($paramsFile)) {
            throw new ConfigurationException("Params file {$paramsFile} not found");
        }

        try {
            if (preg_match('#\.ya?ml$#', $paramStorage)) {
                return $this->loadYamlFile($paramsFile);
            }

            if (preg_match('#\.ini$#', $paramStorage)) {
                return $this->loadIniFile($paramsFile);
            }

            if (preg_match('#\.php$#', $paramStorage)) {
                return $this->loadPhpFile($paramsFile);
            }

            if (preg_match('#(\.env(\.|$))#', $paramStorage)) {
                return $this->loadDotEnvFile($paramsFile);
            }

            if (preg_match('#\.xml$#', $paramStorage)) {
                return $this->loadXmlFile($paramsFile);
            }
        } catch (Exception $e) {
            throw new ConfigurationException("Failed loading params from {$paramStorage}\n" . $e->getMessage());
        }

        throw new ConfigurationException("Params can't be loaded from `{$paramStorage}`.");
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function loadIniFile(string $file): array
    {
        $params = parse_ini_file($file);
        return $this->validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function loadPhpFile(string $file): array
    {
        $params = require $file;
        return $this->validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function loadYamlFile(string $file): array
    {
        $params = Yaml::parse($this->getFileContents($file));
        $params = $this->validateParams($params, $file);

        if (isset($params['parameters'])) { // Symfony style
            $params = $this->validateParams($params['parameters'], $file);
            ;
        }
        return $this->validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function loadXmlFile(string $file): array
    {
        if (!extension_loaded('simplexml')) {
            throw new ConfigurationException('`simplexml` extension is required to parse .xml files.');
        }

        $paramsToArray = function (SimpleXMLElement $params) use (&$paramsToArray): array {
            $a = [];
            foreach ($params as $param) {
                $key = isset($param['key']) ? (string)$param['key'] : $param->getName();
                $type = isset($param['type']) ? (string)$param['type'] : 'string';
                $value = (string)$param;
                $a[$key] = match ($type) {
                    'bool', 'boolean', 'int', 'integer', 'float', 'double' => settype($value, $type),
                    'constant' => constant($value),
                    'collection' => $paramsToArray($param),
                    default => (string) $param,
                };
            }

            return $a;
        };

        $simpleXMLElement = simplexml_load_file($file);
        if ($simpleXMLElement === false) {
            throw new ConfigurationException("Params can't be loaded from `{$file}`.");
        }
        $params  = $paramsToArray($simpleXMLElement);
        return $this->validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function loadDotEnvFile(string $file): array
    {
        // vlucas/phpdotenv
        if (
            class_exists(PhpDotenv::class)
            && class_exists(RepositoryBuilder::class)
            && method_exists(RepositoryBuilder::class, 'createWithDefaultAdapters')
        ) {
            $repository = RepositoryBuilder::createWithDefaultAdapters()->make();
            $dotenv = PhpDotenv::create($repository, codecept_root_dir(), codecept_relative_path($file));

            return $dotenv->load();
        }

        // symfony/dotenv
        if (class_exists(SymfonyDotenv::class)) {
            $symfonyDotEnv = new SymfonyDotenv();
            $values = $symfonyDotEnv->parse($this->getFileContents($file), $file);
            $symfonyDotEnv->populate($values, true);
            return $values;
        }

        throw new ConfigurationException(
            "`vlucas/phpdotenv:5.*` or `symfony/dotenv` library is required to parse .env files.\n" .
            "Please install it via composer, e.g.: composer require vlucas/phpdotenv"
        );
    }

    /**
     * @return array<mixed>
     */
    private function loadEnvironmentVars(): array
    {
        return $_SERVER;
    }

    /**
     * @throws ConfigurationException
     */
    private function getFileContents(string $file): string
    {
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new ConfigurationException("Params can't be loaded from `{$file}`.");
        }
        return $contents;
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private function validateParams(mixed $params, string $file): array
    {
        if (!is_array($params)) {
            throw new ConfigurationException("Params can't be loaded from `{$file}`.");
        }
        return $params;
    }
}
