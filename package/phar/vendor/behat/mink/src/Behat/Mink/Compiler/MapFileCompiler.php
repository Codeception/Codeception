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
 * Class loader map file compiler.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class MapFileCompiler
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
     * Compiles map file and autoloader.
     *
     * @param   string  $version
     */
    public function compile($autoloaderFilename = 'autoload.php', $mapFilename = 'autoload_map.php')
    {
        if (file_exists($mapFilename)) {
            unlink($mapFilename);
        }
        $mappings = '';

        // autoload Symfony2
        $mappings .= "\nif (!defined('BEHAT_AUTOLOAD_SF2') || true === BEHAT_AUTOLOAD_SF2) {\n";
        foreach ($this->findPhpFile()->in($this->libPath . '/vendor/symfony') as $file) {
            $path  = str_replace(array(
                $this->libPath . '/vendor/symfony/browser-kit/',
                $this->libPath . '/vendor/symfony/css-selector/',
                $this->libPath . '/vendor/symfony/dom-crawler/',
                $this->libPath . '/vendor/symfony/process/',
            ), '', $file->getRealPath());
            $class = str_replace(array('/', '.php'), array('\\', ''), $path);
            $mappings .= "    \$mappings['$class'] = '$path';\n";
        }
        $mappings .= "}\n";

        $mapContent = <<<MAP_FILE
<?php

\$mappings = array();
$mappings
return \$mappings;
MAP_FILE;

        file_put_contents($mapFilename, $mapContent);
        file_put_contents($autoloaderFilename, $this->getAutoloadScript($mapFilename));
    }

    /**
     * Returns autoload.php content.
     *
     * @param   string  $mapFilename
     *
     * @return  string
     */
    protected function getAutoloadScript($mapFilename)
    {
        return sprintf(<<<'EOF'
<?php

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Behat\Mink\ClassLoader\MapFileClassLoader')) {
    require_once __DIR__ . '/src/Behat/Mink/ClassLoader/MapFileClassLoader.php';
}

use Behat\Mink\ClassLoader\MapFileClassLoader;

$loader = new MapFileClassLoader(__DIR__ . '/%s');
$loader->register();

require_once __DIR__ . '/vendor/autoload.php';

EOF
        , $mapFilename);
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
