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
    public static function load(array|string $paramStorage): array
    {
        if (is_array($paramStorage)) {
            return $paramStorage;
        }

        if ($paramStorage === 'env' || $paramStorage === 'environment') {
            return self::loadEnvironmentVars();
        }

        $paramsFile = codecept_absolute_path($paramStorage);
        if (!file_exists($paramsFile)) {
            throw new ConfigurationException("Params file {$paramsFile} not found");
        }

        try {
            if (preg_match('#\.ya?ml$#', $paramStorage)) {
                return self::loadYamlFile($paramsFile);
            }

            if (preg_match('#\.ini$#', $paramStorage)) {
                return self::loadIniFile($paramsFile);
            }

            if (preg_match('#\.php$#', $paramStorage)) {
                return self::loadPhpFile($paramsFile);
            }

            if (preg_match('#(\.env(\.|$))#', $paramStorage)) {
                return self::loadDotEnvFile($paramsFile);
            }

            if (preg_match('#\.xml$#', $paramStorage)) {
                return self::loadXmlFile($paramsFile);
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
    private static function loadIniFile(string $file): array
    {
        $params = parse_ini_file($file);
        return self::validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private static function loadPhpFile(string $file): array
    {
        $params = require $file;
        return self::validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private static function loadYamlFile(string $file): array
    {
        $params = Yaml::parse(self::getFileContents($file));
        $params = self::validateParams($params, $file);

        if (isset($params['parameters'])) { // Symfony style
            $params = self::validateParams($params['parameters'], $file);
            ;
        }
        return self::validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private static function loadXmlFile(string $file): array
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
        return self::validateParams($params, $file);
    }

    /**
     * @return array<mixed>
     * @throws ConfigurationException
     */
    private static function loadDotEnvFile(string $file): array
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
            $values = $symfonyDotEnv->parse(self::getFileContents($file), $file);
            $symfonyDotEnv->populate($values);
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
    private static function loadEnvironmentVars(): array
    {
        return $_SERVER;
    }

    /**
     * @throws ConfigurationException
     */
    private static function getFileContents(string $file): string
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
    private static function validateParams(mixed $params, string $file): array
    {
        if (!is_array($params)) {
            throw new ConfigurationException("Params can't be loaded from `{$file}`.");
        }
        return $params;
    }
}
