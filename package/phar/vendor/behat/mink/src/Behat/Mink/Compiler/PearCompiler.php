<?php

namespace Behat\Mink\Compiler;

use Symfony\Component\Finder\Finder;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pear package compiler.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PearCompiler
{
    /**
     * Behat lib directory.
     *
     * @var     string
     */
    private $libPath;

    /**
     * Initializes compiler.
     */
    public function __construct()
    {
        $this->libPath = realpath(__DIR__ . '/../../../../');
    }

    /**
     * Compiles pear package.
     *
     * @param   string  $version
     */
    public function compile($version, $stability)
    {
        if (file_exists('package.xml')) {
            unlink('package.xml');
        }
        file_put_contents('package.xml', $this->getPackageTemplate());

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.xliff')
            ->name('*.xml')
            ->name('*.js')
            ->name('*.feature')
            ->name('LICENSE')
            ->name('LICENSE.txt')
            ->notName('test')
            ->notName('tests')
            ->exclude(array(
                'Compiler',
                'finder',
                'test',
                'tests',
                'vendor',
            ))
            ->in($this->libPath . '/src')
            ->in($this->libPath . '/vendor/composer')
            ->in($this->libPath . '/vendor/alexandresalome')
            ->in($this->libPath . '/vendor/behat')
            ->in($this->libPath . '/vendor/fabpot')
            ->in($this->libPath . '/vendor/kriswallsmith')
            ->in($this->libPath . '/vendor/guzzle')
            ->in($this->libPath . '/vendor/instaclick')
        ;

        $xmlSourceFiles = '';
        foreach ($finder as $file) {
            if (!$file instanceof \SplFileInfo) {
                $file = new \SplFileInfo($file);
            }

            $path = str_replace($this->libPath . '/', '', $file->getRealPath());
            $xmlSourceFiles .=
                '<file role="php" baseinstalldir="mink" install-as="'.$path.'" name="'.$path.'" />'."\n";
        }

        $this->replaceTokens('package.xml', '##', '##', array(
            'MINK_VERSION' => $version,
            'CURRENT_DATE' => date('Y-m-d'),
            'SOURCE_FILES' => $xmlSourceFiles,
            'STABILITY'    => $stability
        ));

        system('pear package');
        unlink('package.xml');
    }

    /**
     * Replaces tokens in specified path.
     *
     * @param   string|array    $files          files array or single file
     * @param   string          $tokenStart     token start symbol
     * @param   string          $tokenFinish    token finish symbol
     * @param   array           $tokens         replace tokens array
     */
    protected function replaceTokens($files, $tokenStart, $tokenFinish, array $tokens)
    {
        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            $content = file_get_contents($file);
            foreach ($tokens as $key => $value) {
                $content = str_replace($tokenStart . $key . $tokenFinish, $value, $content, $count);
            }
            file_put_contents($file, $content);
        }
    }

    /**
     * Returns pear package template.
     *
     * @return  string
     */
    protected function getPackageTemplate()
    {
        return <<<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<package packagerversion="1.8.0" version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
    http://pear.php.net/dtd/tasks-1.0.xsd
    http://pear.php.net/dtd/package-2.0
    http://pear.php.net/dtd/package-2.0.xsd">
    <name>mink</name>
    <channel>pear.behat.org</channel>
    <summary>Behat\Mink is an browser emulation framework for PHP</summary>
    <description>
        Behat\Mink is an open source browser emulation framework for php 5.3.
    </description>
    <lead>
        <name>Konstantin Kudryashov</name>
        <user>everzet</user>
        <email>ever.zet@gmail.com</email>
        <active>yes</active>
    </lead>
    <date>##CURRENT_DATE##</date>
    <version>
        <release>##MINK_VERSION##</release>
        <api>1.0.0</api>
    </version>
    <stability>
        <release>##STABILITY##</release>
        <api>##STABILITY##</api>
    </stability>
    <license uri="http://www.opensource.org/licenses/mit-license.php">MIT</license>
    <notes>-</notes>
    <contents>
        <dir name="/">
            ##SOURCE_FILES##

            <file role="php" baseinstalldir="mink" name="autoload.php" />
            <file role="php" baseinstalldir="mink" name="autoload_map.php" />
            <file role="php" baseinstalldir="mink" name="CHANGES.md" />
            <file role="php" baseinstalldir="mink" name="LICENSE" />
            <file role="php" baseinstalldir="mink" name="README.md" />
        </dir>
    </contents>
    <dependencies>
        <required>
            <php>
                <min>5.3.1</min>
            </php>
            <pearinstaller>
                <min>1.4.0</min>
            </pearinstaller>
            <package>
                <name>BrowserKit</name>
                <channel>pear.symfony.com</channel>
            </package>
            <package>
                <name>CssSelector</name>
                <channel>pear.symfony.com</channel>
            </package>
            <package>
                <name>DomCrawler</name>
                <channel>pear.symfony.com</channel>
            </package>
            <package>
                <name>Process</name>
                <channel>pear.symfony.com</channel>
            </package>
            <extension>
                <name>pcre</name>
            </extension>
            <extension>
                <name>simplexml</name>
            </extension>
            <extension>
                <name>xml</name>
            </extension>
        </required>
        <optional>
            <package>
                <name>PHPUnit</name>
                <channel>pear.phpunit.de</channel>
            </package>
        </optional>
    </dependencies>
    <phprelease />
</package>
EOF;
    }

    /**
     * Creates finder instance to search php files.
     *
     * @return  Symfony\Component\Finder\Finder
     */
    private function findPhpFile()
    {
        $finder = new Finder();

        return $finder->files()->ignoreVCS(true)->name('*.php');
    }
}
