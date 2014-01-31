<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks {

    const BRANCH = '2.0';
    use \Robo\Task\PackPhar;
    use \Robo\Task\SymfonyCommand;

    public function release()
    {
        $this->say("CODECEPTION RELEASE: ".\Codeception\Codecept::VERSION);
        $this->buildDocs();
        $this->publishSite();
        $this->buildPhar();
        $this->publishPhar();
        $this->publishGit();
    }

    public function update()
    {
        $this->clean();
        $this->taskComposerUpdate()->run();
    }

    public function testPhpbrowser()
    {
        $this->taskServer(8000)
            ->background()
            ->dir('tests/data/app')
            ->run();

        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','tests/unit/Codeception/Module/PhpBrowserTest.php')
            ->run();

    }

    public function testFacebook()
    {
        $this->taskServer(8000)
            ->background()
            ->dir('tests/data/app')
            ->run();

        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','tests/unit/Codeception/Module/FacebookTest.php')
            ->run();

    }

    public function testWebdriver($pathToSelenium = '~/selenium-server-standalone-2.39.0.jar ')
    {
        $this->taskServer(8000)
            ->background()
            ->dir('tests/data/app')
            ->run();

        $this->taskExec('java -jar '.$pathToSelenium)
            ->background()
            ->run();

        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','tests/unit/Codeception/Module/WebDriverTest.php')
            ->run();
    }

    public function testCli()
    {
        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','cli')
            ->run();

        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','tests/unit/Codeception/Command')
            ->run();
    }

    /**
     * @desc creates codecept.phar
     * @throws Exception
     */
    public function buildPhar()
    {
        $pharTask = $this->taskPackPhar('package/codecept.phar')
            ->compress()
            ->stub('package/stub.php');

        $finder = Finder::create()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.tpl.dist')
            ->name('*.html.dist')
            ->in('src');

        foreach ($finder as $file) {
            $pharTask->addFile('src/'.$file->getRelativePathname(), $file->getRealPath());
        }

        $finder = Finder::create()->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.css')
            ->name('*.png')
            ->name('*.js')
            ->name('*.css')
            ->name('*.png')
            ->name('*.tpl.dist')
            ->name('*.html.dist')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('benchmark')
            ->exclude('demo')
            ->in('vendor');


        foreach ($finder as $file) {
            $pharTask->addStripped('vendor/'.$file->getRelativePathname(), $file->getRealPath());
        }

        $pharTask->addFile('autoload.php', 'autoload.php')
            ->addFile('codecept', 'package/bin')
            ->run();
        
        $code = $this->taskExec('php package/codecept.phar')->run();
        if ($code !== 0) {
            throw new Exception("There was problem compiling phar");
        }
    }

    /**
     * @desc generates modules reference from source files
     */
    public function buildDocs()
    {
        $this->taskCleanDir('docs/modules')->run();
        $this->say('generating documentation from source files');

        $clean_doc = function($doc, $indent = 3) {
            $lines = explode("\n", $doc);
            $lines = array_map(function ($line) use ($indent) {
                return substr($line, $indent);
            }, $lines);
            $doc = implode("\n", $lines);
            $doc = str_replace(array('@since'), array(' * available since version'), $doc);
            $doc = str_replace(array(' @', "\n@"), array("  * ", "\n * "), $doc);
            return $doc;
        };

        $modules = Finder::create()->files('*.php')->in(__DIR__ . '/src/Codeception/Module');

        foreach ($modules as $module) {

            $moduleName = basename(substr($module, 0, -4));
            $text = '# ' . $moduleName . " Module\n";
            $this->say($moduleName);

            $text .= "**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/$moduleName.php)**\n\n";

            $className = '\Codeception\Module\\' . $moduleName;
            $class = new ReflectionClass($className);
            $doc = $class->getDocComment();
            if ($doc) $text .= $clean_doc($doc, 3);
            $text .= "\n## Actions\n\n";

            $reference = array();
            foreach ($class->getMethods() as $method) {
                if ($method->isConstructor() or $method->isDestructor()) continue;
                if (strpos($method->name, '_') === 0) continue;
                if ($method->isPublic()) {
                    $title = "\n### " . $method->name . "\n\n";
                    $doc = $method->getDocComment();
                    if (!$doc) {
                        $interfaces = $class->getInterfaces();
                        foreach ($interfaces as $interface) {
                            $i = new \ReflectionClass($interface->name);
                            if ($i->hasMethod($method->name)) {
                                $doc = $i->getMethod($method->name)->getDocComment();
                                break;
                            }
                        }
                    }

                    if (!$doc) {
                        $parent = new \ReflectionClass($class->getParentClass()->name);
                        if ($parent->hasMethod($method->name)) {
                            $doc = $parent->getMethod($method->name)->getDocComment();
                        }
                    }
                    $doc = $doc ? $clean_doc($doc, 7) : "__not documented__\n";
                    $reference[$method->name] = $title . $doc;
                }
            }
            ksort($reference);
            $text .= implode("\n", $reference);

            file_put_contents(__DIR__ . '/docs/modules/' . $moduleName . '.md', $text);

        }
    }

    /**
     * @desc publishes generated phar to codeception.com
     */
    public function publishPhar()
    {
        $this->cloneSite();
        $version = \Codeception\Codecept::VERSION;
        if (strpos($version, self::BRANCH) === 0) {
            $this->say("publishing to release branch");
            copy('../codecept.phar','codecept.phar');
        }

        @mkdir("releases/$version");
        copy('codecept.phar',"releases/$version/codecept.phar");

        $this->taskExec('git add codecept.phar')->run();
        $this->taskExec('git add releases/$version/codecept.phar')->run();
        $this->publishSite();
    }

    /**
     * @desc updates docs on codeception.com
     */
    public function publishDocs()
    {
        if (strpos(\Codeception\Codecept::VERSION, self::BRANCH) !== 0) {
            $this->say("The ".\Codeception\Codecept::VERSION." is not in release branch. Site is not build");
            return;
        }
        $this->say('building site...');

        $docs = Finder::create()->files('*.md')->sortByName()->in('docs');
        $this->cloneSite();

        $modules = array();
        $api = array();
        foreach ($docs as $doc) {
            $newfile = $doc->getFilename();
            $name = $doc->getBasename();
            $contents = $doc->getContents();
            if (strpos($doc->getPathname(),'docs'.DIRECTORY_SEPARATOR.'modules')) {
                $newfile = 'docs/modules/'.$newfile;
                $modules[$name] = '/docs/modules/'.$doc->getBasename();
                $contents = str_replace('## ','### ', $contents);
            } else {
                $newfile = 'docs/'.$newfile;
                $api[substr($name,3)] = '/docs/'.$doc->getBasename();
            }

            copy($doc->getPathname(), $newfile);

            $contents = preg_replace('~```\s?php(.*?)```~ms',"{% highlight php %}\n$1\n{% endhighlight %}", $contents);
            $contents = preg_replace('~```\s?html(.*?)```~ms',"{% highlight html %}\n$1\n{% endhighlight %}", $contents);
            $contents = preg_replace('~```(.*?)```~ms',"{% highlight yaml %}\n$1\n{% endhighlight %}", $contents);
            $matches = array();
            $title = "";

            // Extracting page h1 to re-use in <title>
            if (preg_match('/^# (.*)$/m', $contents, $matches)) {
              $title = $matches[1];
            }
            $contents = "---\nlayout: doc\ntitle: ".($title!="" ? $title." - " : "")."Codeception - Documentation\n---\n\n".$contents;
            file_put_contents($newfile, $contents);
        }

        $guides = array_keys($api);
        foreach ($api as $name => $url) {
            $filename = substr($url, 6);
            $doc = file_get_contents('docs/'.$filename.'.md')."\n\n\n";
            $i = array_search($name, $guides);
            if (isset($guides[$i+1])) {
                $next_title = $guides[$i+1];
                $next_url = $api[$guides[$i+1]];
                $doc .= "\n* **Next Chapter: [$next_title >]($next_url)**";
            }

            if (isset($guides[$i-1])) {
                $prev_title = $guides[$i-1];
                $prev_url = $api[$guides[$i-1]];
                $doc .= "\n* **Previous Chapter: [< $prev_title]($prev_url)**";
            }
            file_put_contents('docs/'.$filename.'.md', $doc);
        }


        $guides_list = '';
        foreach ($api as $name => $url) {
            $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $name);
            $name = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $name);
            $guides_list.= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }

        file_put_contents('_includes/guides.html', $guides_list);

        $modules_list = '';
        foreach ($modules as $name => $url) {
            $modules_list.= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }

        file_put_contents('_includes/modules.html', $modules_list);
        $this->publishSite();
        $this->taskExec('git add')->args('.')->run();
    }

    /**
     * @desc creates a new version tag and pushes to github
     */
    public function publishGit()
    {
        $version = \Codeception\Codecept::VERSION;
        $this->say('creating new tag for '.$version);
        $branch = explode('.', $version);
        array_pop($branch);
        $branch = implode('.',$branch);
        $this->taskExec("git tag $version")->run();
        $this->taskExec("git push origin $branch --tags")->run();
    }

    /**
     * @desc cleans all log and temp directories
     */
    public function clean()
    {
        $this->taskCleanDir([
            'tests/log',
            'tests/data/claypit/tests/_log',
            'tests/data/included/_log',
            'tests/data/included/jazz/tests/_log',
            'tests/data/included/shire/tests/_log',
        ])->run();

        $this->taskDeleteDir([
            'tests/data/claypit/c3tmp',
            'tests/data/sandbox'
        ])->run();
    }

    public function buildGuys()
    {
        $build = 'php codecept build';
        $this->taskExec($build)->run();
        $this->taskExec($build)->args('-c tests/data/claypit')->run();
        $this->taskExec($build)->args('-c tests/data/included')->run();
        $this->taskExec($build)->args('-c tests/data/included/jazz')->run();
        $this->taskExec($build)->args('-c tests/data/included/shire')->run();
        $this->taskExec($build)->args('-c tests/data/included/jazz')->run();
    }

    protected function cloneSite()
    {
        @mkdir("package/site");
        $this->taskExec('git clone')
            ->args('git@github.com:Codeception/codeception.github.com.git')
            ->args('package/site/')
            ->run();
        chdir('package/site');
    }

    protected function publishSite()
    {
        $this->taskExec('git commit')->args('-m "auto updated documentation"')->run();
        $this->taskExec('git push')->run();

        chdir('..');
        sleep(2);
        $this->taskDeleteDir('site')->run();
        chdir('..');
        $this->say("Site build succesfully");
    }

} 