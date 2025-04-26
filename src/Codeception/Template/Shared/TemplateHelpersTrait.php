<?php

declare(strict_types=1);

namespace Codeception\Template\Shared;

trait TemplateHelpersTrait
{
    protected function createSuiteDirs(string $dir): void
    {
        $paths = ['_output','Support','Support/Data','Support/_generated'];
        foreach ($paths as $sub) {
            $full = $dir . DIRECTORY_SEPARATOR . $sub;
            if ($sub === 'Support/Data') {
                $this->createEmptyDirectory($full);
            } elseif (str_ends_with($sub, '_generated')) {
                $this->createEmptyDirectory($full);
                $this->gitIgnore($full);
            } else {
                $this->createDirectoryFor($full);
                if ($sub === '_output') {
                    $this->gitIgnore($full);
                }
            }
        }
    }

    /**
     * @param string[] $modules
     */
    protected function ensureModules(array $modules): void
    {
        $toInstall = [];
        foreach ($modules as $module) {
            $class = '\\Codeception\\Module\\' . $module;
            if (!class_exists($class)) {
                $toInstall[] = $module;
            }
        }
        if ($toInstall !== []) {
            $this->addModulesToComposer($toInstall);
        }
    }
}
