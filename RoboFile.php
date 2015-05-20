<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Finder\Finder;
use \Robo\Task\Development\GenerateMarkdownDoc as Doc;

class RoboFile extends \Robo\Tasks
{
    const STABLE_BRANCH = '2.0';

    public function release()
    {
        $this->say("CODECEPTION RELEASE: ".\Codeception\Codecept::VERSION);
        $this->update();
        $this->buildDocs();
        $this->publishDocs();
        $this->buildPhar();
        $this->publishPhar();
        $this->publishGit();
        $this->versionBump();
    }

    public function versionBump($version = '')
    {
        if (!$version) {
            $versionParts = explode('.', \Codeception\Codecept::VERSION);
            $versionParts[count($versionParts)-1]++;
            $version = implode('.', $versionParts);
        }
        $this->say("Bumping version to $version");
        $this->taskReplaceInFile('src/Codeception/Codecept.php')
            ->from(\Codeception\Codecept::VERSION)
            ->to($version)
            ->run();
    }

    public function update()
    {
        $this->clean();
        $this->taskComposerUpdate()->dir('tests/data/claypit')->run();
        $this->taskComposerUpdate()->run();
    }

    public function changed($change)
    {
        $this->taskChangelog()
            ->version(\Codeception\Codecept::VERSION)
            ->change($change)
            ->run();
    }

    protected function server()
    {
        $this->taskServer(8000)
            ->background()
            ->dir('tests/data/app')
            ->run();
    }

    public function testPhpbrowser($args = '', $opt = ['test|t' => null])
    {
        $test = $opt['test'] ? ':'.$opt['test'] : '';
        $this->server();
        $this->taskCodecept('./codecept')
            ->args($args)
            ->test('tests/unit/Codeception/Module/PhpBrowserTest.php'.$test)
            ->run();
    }

    public function testRestBrowser($args = '', $opt = ['test|t' => null])
    {
        $test = $opt['test'] ? ':'.$opt['test'] : '';
        $this->taskServer(8010)
            ->background()
            ->dir('tests/data')
            ->run();

        $this->taskCodecept('./codecept')
            ->test('tests/unit/Codeception/Module/PhpBrowserRestTest.php'.$test)
            ->args($args)
            ->run();
    }

    public function testCoverage()
    {
        $this->server();
        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite','coverage')
            ->run();
    }

    public function testWebdriver($args = '', $opt = ['test|t' => null])
    {
        $test = $opt['test'] ? ':'.$opt['test'] : '';
        $container = $this->taskDockerRun('davert/selenium-env')
            ->detached()
            ->publish(4444,4444)
            ->env('APP_PORT', 8000)
            ->run();

        $this->taskServer(8000)
            ->dir('tests/data/app')
            ->background()
            ->host('0.0.0.0')
            ->run();

        sleep(3); // wait for selenium to launch

        $this->taskCodecept('./codecept')
            ->test('tests/unit/Codeception/Module/WebDriverTest.php'.$test)
            ->args($args)
            ->run();
        
        $this->taskDockerStop($container)->run();
    }

    public function testLaunchServer($pathToSelenium = '~/selenium-server.jar ')
    {
        $this->taskExec('java -jar '.$pathToSelenium)
            ->background()
            ->run();

        $this->taskServer(8010)
            ->background()
            ->dir('tests/data/rest')
            ->run();
        $this->taskServer(8000)
            ->dir('tests/data/app')
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
            ->name('*.eot')
            ->name('*.svg')
            ->name('*.ttf')
            ->name('*.wof')
            ->name('*.woff')
            ->name('*.png')
            ->name('*.tpl.dist')
            ->name('*.html.dist')
            ->exclude('videlalvaro')
            ->exclude('pheanstalk')
            ->exclude('phpseclib')
            ->exclude('codegyre')
            ->exclude('monolog')
            ->exclude('phpspec')
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
        
        $code = $this->taskExec('php package/codecept.phar')->run()->getExitCode();
        if ($code !== 0) {
            throw new Exception("There was problem compiling phar");
        }
    }

    /**
     * @desc generates modules reference from source files
     */
    public function buildDocs()
    {
        $this->say('generating documentation from source files');
        $this->buildDocsModules();
        $this->buildDocsUtils();
        $this->buildDocsCommands();
    }

    public function buildDocsModules()
    {
        $this->taskCleanDir('docs/modules')->run();
        $this->say("Modules");
        $modules = Finder::create()->files()->name('*.php')->in(__DIR__ . '/src/Codeception/Module');

        foreach ($modules as $module) {
            $moduleName = basename(substr($module, 0, -4));
            $className = '\Codeception\Module\\' . $moduleName;
            $source = "https://github.com/Codeception/Codeception/tree/".self::STABLE_BRANCH."/src/Codeception/Module/$moduleName.php";

            $this->taskGenDoc('docs/modules/' . $moduleName . '.md')
                ->docClass($className)
                ->prepend("# $moduleName Module\n\n**For additional reference, please review the [source]($source)**")
                ->append('<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="'.$source.'">Help us to improve documentation. Edit module reference</a></div>')
                ->processClassSignature(false)
                ->processProperty(false)
                ->filterMethods(function(\ReflectionMethod $method) {
                    if ($method->isConstructor() or $method->isDestructor()) return false;
                    if (!$method->isPublic()) return false;
                    if (strpos($method->name, '_') === 0) return false;
                    return true;
                })->processMethod(function(\ReflectionMethod $method, $text) {
                    $title = "\n### {$method->name}\n";
                    if (!trim($text)) return $title."__not documented__\n";
                    $text = str_replace(array('@since'), array(' * available since version'), $text);
                    $text = preg_replace('~@throws(.*?)~', '', $text);
                    $text = str_replace("@return mixed\n", '', $text);
                    $text = str_replace(array("\n @"), array("\n * "), $text);
                    return $title . $text;
                })->processMethodSignature(false)
                ->reorderMethods('ksort')
                ->run();
        }
    }

    public function buildDocsUtils()
    {
        $this->say("Util Classes");
        $utils = ['Autoload', 'Fixtures', 'Stub', 'Locator', 'XmlBuilder'];

        foreach ($utils as $utilName) {
            $className = '\Codeception\Util\\' . $utilName;
            $source = "https://github.com/Codeception/Codeception/blob/".self::STABLE_BRANCH."/src/Codeception/Util/$utilName.php";

            $this->taskGenDoc('docs/reference/' . $utilName . '.md')
                ->docClass($className)
                ->append('<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="'.$source.'">Help us to improve documentation. Edit module reference</a></div>')
                ->processClassDocBlock(function(ReflectionClass $r, $text) {
                    return $text . "\n";
                })->processMethodDocBlock(function(ReflectionMethod $r, $text) use ($utilName, $source) {
                    $line = $r->getStartLine();
                    $text = preg_replace("~@(.*?)([$\s])~",' * `$1` $2', $text);
                    $text .= "\n[See source]($source#L$line)";
                    return "\n" . $text."\n";
                })
                ->reorderMethods('ksort')
                ->run();
        }
    }

    public function buildDocsCommands()
    {
        $this->say("Commands");

        $commands = Finder::create()->files()->name('*.php')->depth(0)->in(__DIR__ . '/src/Codeception/Command');

        $commandGenerator = $this->taskGenDoc('docs/reference/Commands.md');
        foreach ($commands as $command) {
            $commandName = basename(substr($command, 0, -4));
            $className = '\Codeception\Command\\' . $commandName;
            $commandGenerator->docClass($className);
        }
        $commandGenerator
            ->prepend("# Console Commands\n")
            ->processClassSignature(function ($r, $text) { return "## ".$r->getShortName();  })
            ->filterMethods(function(ReflectionMethod $r) { return false; })
            ->run();

    }

    /**
     * @desc publishes generated phar to codeception.com
     */
    public function publishPhar()
    {
        $this->cloneSite();
        $version = \Codeception\Codecept::VERSION;
        if (strpos($version, self::STABLE_BRANCH) === 0) {
            $this->say("publishing to release branch");
            copy('../codecept.phar','codecept.phar');
            $this->taskExec('git add codecept.phar')->run();
        }

        $this->taskFileSystemStack()
            ->mkdir("releases/$version")
            ->copy('../codecept.phar',"releases/$version/codecept.phar")
            ->run();

        $this->taskGitStack()->add('-A')->run();

        $releases = array_reverse(iterator_to_array(Finder::create()->directories()->sortByName()->in('releases')));
        $branch = null;
        $releaseFile = $this->taskWriteToFile('builds.markdown')
            ->line('---')
            ->line('layout: page')
            ->line('title: Codeception Builds')
            ->line('---')
            ->line('');


        foreach ($releases as $release) {
            $releaseName = $release->getBasename();
            list($major, $minor) = explode('.', $releaseName);
            if ("$major.$minor" != $branch) {
                $branch = "$major.$minor";
                $releaseFile->line("\n## $branch");
                if ($major < 2) {
                    $releaseFile->line("*Requires: PHP 5.3 and higher + CURL*\n");
                } else {
                    $releaseFile->line("*Requires: PHP 5.4 and higher + CURL*\n");
                }
                $releaseFile->line("* **[Download Latest $branch Release](http://codeception.com/releases/$releaseName/codecept.phar)**");
            }
            $releaseFile->line("* [$releaseName](http://codeception.com/releases/$releaseName/codecept.phar)");
        }
        $releaseFile->run();

        $this->publishSite();
    }

    /**
     * Updates docs on codeception.com
     *
     */
    public function publishDocs()
    {
        if (strpos(\Codeception\Codecept::VERSION, self::STABLE_BRANCH) !== 0) {
            $this->say("The ".\Codeception\Codecept::VERSION." is not in release branch. Site is not build");
            return;
        }
        $this->say('building site...');

        $this->cloneSite();
        $this->taskCleanDir('docs')
            ->run();
        $this->taskFileSystemStack()
            ->mkdir('docs/reference')
            ->mkdir('docs/modules')
            ->run();

        chdir('../..');

        $this->taskWriteToFile('package/site/changelog.markdown')
            ->line('---')
            ->line('layout: page')
            ->line('title: Codeception Changelog')
            ->line('---')
            ->line('')
            ->line('<div class="alert alert-warning">Download specific version at <a href="/builds">builds page</a></div>')
            ->line('')
            ->line($this->processChangelog())
            ->run();

        $docs = Finder::create()->files('*.md')->sortByName()->in('docs');

        $modules = array();
        $api = array();
        $reference = array();
        foreach ($docs as $doc) {
            $newfile = $doc->getFilename();
            $name = substr($doc->getBasename(),0,-3);
            $contents = $doc->getContents();
            if (strpos($doc->getPathname(),'docs'.DIRECTORY_SEPARATOR.'modules') !== false) {
                $newfile = 'docs/modules/' . $newfile;
                $modules[$name] = '/docs/modules/' . $doc->getBasename();
                $contents = str_replace('## ', '### ', $contents);
            } elseif(strpos($doc->getPathname(),'docs'.DIRECTORY_SEPARATOR.'reference') !== false) {
                $newfile = 'docs/reference/' . $newfile;
                $reference[$name] = '/docs/reference/' . $doc->getBasename();
            } else {
                $newfile = 'docs/'.$newfile;
                $api[substr($name,3)] = '/docs/'.$doc->getBasename();
            }

            copy($doc->getPathname(), 'package/site/' . $newfile);

            $highlight_languages = implode('|', array('php', 'html', 'bash', 'yaml', 'json', 'xml', 'sql'));
            $contents = preg_replace("~```\s?($highlight_languages)\b(.*?)```~ms", "{% highlight $1 %}\n$2\n{% endhighlight %}", $contents);
            $contents = str_replace('{% highlight  %}','{% highlight yaml %}', $contents);
            $contents = preg_replace("~```\s?(.*?)```~ms", "{% highlight yaml %}\n$1\n{% endhighlight %}", $contents);
            // set default language in order not to leave unparsed code inside '```'

            $matches = array();
            $title = "";
            // Extracting page h1 to re-use in <title>
            if (preg_match('/^# (.*)$/m', $contents, $matches)) {
              $title = $matches[1];
            }
            $contents = "---\nlayout: doc\ntitle: ".($title!="" ? $title." - " : "")."Codeception - Documentation\n---\n\n".$contents;
            file_put_contents('package/site/' .$newfile, $contents);
        }
        chdir('package/site');
        $guides = array_keys($api);
        foreach ($api as $name => $url) {
            $filename = substr($url, 6);
            $doc = file_get_contents('docs/'.$filename)."\n\n\n";
            $i = array_search($name, $guides);
            if (isset($guides[$i+1])) {
                $next_title = $guides[$i+1];
                $next_url = $api[$guides[$i+1]];
                $next_url = substr($next_url, 0, -3);
                $doc .= "\n* **Next Chapter: [$next_title >]($next_url)**";
            }

            if (isset($guides[$i-1])) {
                $prev_title = $guides[$i-1];
                $prev_url = $api[$guides[$i-1]];
                $prev_url = substr($prev_url, 0, -3);
                $doc .= "\n* **Previous Chapter: [< $prev_title]($prev_url)**";
            }
            $doc .= '<p>&nbsp;</p><div class="alert alert-warning">Docs are incomplete? Outdated? Or you just found a typo? <a href="https://github.com/Codeception/Codeception/tree/'.self::STABLE_BRANCH.'/docs">Help us to improve documentation. Edit it on GitHub</a></div>';

            file_put_contents('docs/'.$filename, $doc);
        }


        $guides_list = '';
        foreach ($api as $name => $url) {
            $url = substr($url, 0, -3);
            $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $name);
            $name = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $name);
            $guides_list .= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        file_put_contents('_includes/guides.html', $guides_list);

        /**
         * Align modules in two columns like this:
         * A D
         * B E
         * C
         */
        $modules_cols = 2;
        $modules_rows = ceil(count($modules) / $modules_cols);
        $module_names_chunked = array_chunk(array_keys($modules), $modules_rows);
        $modules_list = '';
        for ($i = 0; $i < $modules_rows; $i++) {
            for ($j = 0; $j < $modules_cols; $j++) {
                if (isset($module_names_chunked[$j][$i])) {
                    $name = $module_names_chunked[$j][$i];
                    $url = substr($modules[$name], 0, -3);
                    $modules_list .= '<li><a href="'.$url.'">'.$name.'</a></li>';
                }
            }
        }
        file_put_contents('_includes/modules.html', $modules_list);

        $reference_list = '';
        foreach ($reference as $name => $url) {
            $url = substr($url, 0, -3);
            $reference_list .= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        file_put_contents('_includes/reference.html', $reference_list);

        $this->publishSite();
        $this->taskExec('git add')->args('.')->run();
    }

    /**
     * @desc creates a new version tag and pushes to github
     * @param null $branch
     * @param array $opt
     */
    public function publishGit($branch = null, $opt = ['tag|t' => null])
    {
        $version = isset($opt['tag']) ? $opt['tag'] : \Codeception\Codecept::VERSION;
        $this->say('creating new tag for '.$version);
        if (!$branch) {
            $branch = explode('.', $version);
            array_pop($branch);
            $branch = implode('.',$branch);
        }
        $this->taskExec("git tag $version")->run();
        $this->taskExec("git push origin $branch --tags")->run();
    }

    protected function processChangelog()
    {
        $changelog = file_get_contents('CHANGELOG.md');
        $changelog = preg_replace('~@(\w+)~', '<strong><a href="https://github.com/$1">@$1</a></strong>', $changelog); //user
        $changelog = preg_replace('~#(\d+)~', '<a href="https://github.com/Codeception/Codeception/issues/$1">#$1</a>', $changelog); //issue
        $changelog = preg_replace('~\[(\w+)\]~', '<strong>[$1]</strong>', $changelog); //module
        return $changelog;
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

    public function buildActors()
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
        $this->taskGitStack()
            ->add('-A')
            ->commit('auto updated documentation')
            ->push()
            ->run();

        chdir('..');
        sleep(2);
        $this->taskDeleteDir('site')->run();
        chdir('..');
        $this->say("Site build succesfully");
    }

} 
