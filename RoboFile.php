<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Finder\Finder;
use \Robo\Task\Development\GenerateMarkdownDoc as Doc;

class RoboFile extends \Robo\Tasks
{
    const STABLE_BRANCH = '2.2';
    const REPO_BLOB_URL = 'https://github.com/Codeception/Codeception/blob';

    public function release()
    {
        $this->say("CODECEPTION RELEASE: ".\Codeception\Codecept::VERSION);
        $this->update();
        $this->buildDocs();
        $this->publishDocs();

        $this->buildPhar54();
        $this->buildPhar();
        $this->revertComposerJsonChanges();
        $this->publishPhar();
        $this->publishGit();
        $this->publishBase();
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
            ->arg('suite', 'coverage')
            ->run();
    }

    public function testWebdriver($args = '', $opt = ['test|t' => null])
    {
        $test = $opt['test'] ? ':'.$opt['test'] : '';
        $container = $this->taskDockerRun('davert/selenium-env')
            ->detached()
            ->publish(4444, 4444)
            ->env('APP_PORT', 8000)
            ->run();

        $this->taskServer(8000)
            ->dir('tests/data/app')
            ->background()
            ->host('0.0.0.0')
            ->run();

        sleep(3); // wait for selenium to launch

        $this->taskCodecept('./codecept')
            ->test('tests/web/WebDriverTest.php'.$test)
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
            ->arg('suite', 'cli')
            ->run();

        $this->taskSymfonyCommand(new \Codeception\Command\Run('run'))
            ->arg('suite', 'tests/unit/Codeception/Command')
            ->run();
    }

    private function installDependenciesForPhp54()
    {
        $this->taskReplaceInFile('composer.json')
            ->regex('/"platform": \{.*?\}/')
            ->to('"platform": {"php": "5.4.0"}')
            ->run();

        $this->taskComposerUpdate()->run();
    }

    private function installDependenciesForPhp56()
    {
        $this->taskReplaceInFile('composer.json')
            ->regex('/"platform": \{.*?\}/')
            ->to('"platform": {"php": "5.6.0"}')
            ->run();

        $this->taskComposerUpdate()->run();
    }

    private function revertComposerJsonChanges()
    {
        $this->taskReplaceInFile('composer.json')
            ->regex('/"platform": \{.*?\}/')
            ->to('"platform": {}')
            ->run();
    }


    /**
     * @desc creates codecept.phar
     * @throws Exception
     */
    public function buildPhar()
    {
        $this->packPhar('package/codecept.phar');
    }

    /**
     * @desc creates codecept.phar with Guzzle 5.3 and Symfony 2.8
     * @throws Exception
     */
    public function buildPhar54()
    {
        if (!file_exists('package/php54')) {
            mkdir('package/php54');
        }
        $this->installDependenciesForPhp54();
        $this->packPhar('package/php54/codecept.phar');
        $this->installDependenciesForPhp56();
    }

    private function packPhar($pharFileName)
    {
        $pharTask = $this->taskPackPhar($pharFileName)
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

        $finder = Finder::create()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in('ext');

        foreach ($finder as $file) {
            $pharTask->addFile('ext/'.$file->getRelativePathname(), $file->getRealPath());
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
            ->name('*.woff2')
            ->name('*.png')
            ->name('*.tpl.dist')
            ->name('*.html.dist')
            ->exclude('videlalvaro')
            ->exclude('php-amqplib')
            ->exclude('pheanstalk')
            ->exclude('phpseclib')
            ->exclude('codegyre')
            ->exclude('monolog')
            ->exclude('phpspec')
            ->exclude('squizlabs')
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
            ->addFile('shim.php', 'shim.php')
            ->run();
        
        $code = $this->taskExec('php ' . $pharFileName)->run()->getExitCode();
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
        $this->buildDocsExtensions();
    }

    public function buildDocsModules()
    {
        $this->taskCleanDir('docs/modules')->run();
        $this->say("Modules");
        $modules = Finder::create()->files()->name('*.php')->in(__DIR__ . '/src/Codeception/Module');

        foreach ($modules as $module) {
            $moduleName = basename(substr($module, 0, -4));
            $className = 'Codeception\Module\\' . $moduleName;
            $source = "https://github.com/Codeception/Codeception/tree/"
                .self::STABLE_BRANCH."/src/Codeception/Module/$moduleName.php";

            $this->taskGenDoc('docs/modules/' . $moduleName . '.md')
                ->docClass($className)
                ->prepend('# '.$moduleName)
                ->append('<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="'.$source.'">Help us to improve documentation. Edit module reference</a></div>')
                ->processClassSignature(false)
                ->processClassDocBlock(function(\ReflectionClass $c, $text) {
                  return "$text\n\n## Actions";
                })->processProperty(false)
                ->filterMethods(function(\ReflectionMethod $method) use ($className) {
                    if ($method->isConstructor() or $method->isDestructor()) return false;
                    if (!$method->isPublic()) return false;
                    if (strpos($method->name, '_') === 0) {
                        $doc = $method->getDocComment();
                        try {
                            $doc = $doc . $method->getPrototype()->getDocComment();
                        } catch (\ReflectionException $e) {
                        }

                        if (strpos($doc, '@api') === false) {
                            return false;
                        }
                    };
                    return true;
                })->processMethod(function (\ReflectionMethod $method, $text) use ($className, $moduleName) {
                    $title = "\n### {$method->name}\n";
                    if (strpos($method->name, '_') === 0) {
                        $text = str_replace("@api\n", '', $text);
                        $text = "\n*hidden API method, expected to be used from Helper classes*\n" . $text;
                        $text = str_replace("{{MODULE_NAME}}", $moduleName, $text);
                    };

                    if (!trim($text)) {
                        return $title . "__not documented__\n";
                    }

                    $text = str_replace(
                        ['@since', '@version'],
                        [' * `Available since`', ' * `Available since`'],
                        $text
                    );
                    $text = str_replace('@part ', ' * `[Part]` ', $text);
                    $text = str_replace("@return mixed\n", '', $text);
                    $text = preg_replace('~@return (.*?)~', ' * `return` $1', $text);
                    $text = preg_replace("~@(.*?)([$\s])~", ' * `$1` $2', $text);
                    return $title . $text;
                })->processMethodSignature(false)
                ->reorderMethods('ksort')
                ->run();
        }
    }

    public function buildDocsUtils()
    {
        $this->say("Util Classes");
        $utils = ['Autoload', 'Fixtures', 'Stub', 'Locator', 'XmlBuilder', 'JsonType', 'HttpCode'];

        foreach ($utils as $utilName) {
            $className = '\Codeception\Util\\' . $utilName;
            $source = self::REPO_BLOB_URL."/".self::STABLE_BRANCH."/src/Codeception/Util/$utilName.php";

            $this->taskGenDoc('docs/reference/' . $utilName . '.md')
                ->docClass($className)
                ->append(
                    '<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. '
                    .'<a href="'.$source.'">Help us to improve documentation. Edit module reference</a></div>'
                )
                ->processClassDocBlock(function (ReflectionClass $r, $text) {
                    return $text . "\n";
                })->processMethodSignature(function(ReflectionMethod $r, $text) {
                    return '### ' . $r->getName();
                })->processMethodDocBlock(function(ReflectionMethod $r, $text) use ($utilName, $source) {
                    $line = $r->getStartLine();
                    if ($r->isStatic()) {
                        $text = "\n*static*\n$text";
                    }
                    $text = preg_replace("~@(.*?)([$\s])~", ' * `$1` $2', $text);
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
            ->processClassSignature(function ($r, $text) {
                return "## ".$r->getShortName();
            })
            ->filterMethods(function (ReflectionMethod $r) {
                return false;
            })
            ->run();

    }

    public function buildDocsExtensions()
    {
        $this->say('Extensions');

        $extensions = Finder::create()->files()->sortByName()->name('*.php')->in(__DIR__ . '/ext');

        $extGenerator= $this->taskGenDoc(__DIR__.'/ext/README.md');
        foreach ($extensions as $command) {
            $commandName = basename(substr($command, 0, -4));
            $className = '\Codeception\Extension\\' . $commandName;
            $extGenerator->docClass($className);
        }
        $extGenerator
            ->prepend("# Official Extensions\n")
            ->processClassSignature(function ($r, $text) {
                return "## ".$r->getName();
            })
            ->filterMethods(function (ReflectionMethod $r) {
                return false;
            })
            ->filterProperties(function ($r) {
                return false;
            })
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
            copy('../codecept.phar', 'codecept.phar');
            if (!is_dir('php54')) {
                mkdir('php54');
            }
            copy('../php54/codecept.phar', 'php54/codecept.phar');
            $this->taskExec('git add codecept.phar')->run();
            $this->taskExec('git add php54/codecept.phar')->run();
        }

        $this->taskFileSystemStack()
            ->mkdir("releases/$version")
            ->mkdir("releases/$version/php54")
            ->copy('../codecept.phar', "releases/$version/codecept.phar")
            ->copy('../php54/codecept.phar', "releases/$version/php54/codecept.phar")
            ->run();

        $this->taskGitStack()->add('-A')->run();

        $sortByVersion = function (\SplFileInfo $a, \SplFileInfo $b) {
            return version_compare($a->getBaseName(), $b->getBaseName());
        };

        $releases = array_reverse(
            iterator_to_array(Finder::create()->depth(0)->directories()->sort($sortByVersion)->in('releases'))
        );
        $branch = null;
        $releaseFile = $this->taskWriteToFile('builds.markdown')
            ->line('---')
            ->line('layout: page')
            ->line('title: Codeception Builds')
            ->line('---')
            ->line('');


        foreach ($releases as $release) {
            $releaseName = $release->getBasename();
            $downloadUrl = "http://codeception.com/releases/$releaseName/codecept.phar";
            
            list($major, $minor) = explode('.', $releaseName);
            if ("$major.$minor" != $branch) {
                $branch = "$major.$minor";
                $releaseFile->line("\n## $branch");
                if ($major < 2) {
                    $releaseFile->line("*Requires: PHP 5.3 and higher + CURL*\n");
                } else {
                    $releaseFile->line("*Requires: PHP 5.4 and higher + CURL*\n");
                }
                $releaseFile->line("* **[Download Latest $branch Release]($downloadUrl)**");
            }
            $versionLine = "* [$releaseName]($downloadUrl)";

            if (file_exists("releases/$releaseName/php54/codecept.phar")) {
                $downloadUrl = "http://codeception.com/releases/$releaseName/php54/codecept.phar";
                $versionLine .= ", [for PHP 5.4 or 5.5]($downloadUrl)";
            }

            $releaseFile->line($versionLine);
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
            ->line(
                '<div class="alert alert-warning">Download specific version at <a href="/builds">builds page</a></div>'
            )
            ->line('')
            ->line($this->processChangelog())
            ->run();

        $docs = Finder::create()->files('*.md')->sortByName()->in('docs');

        $modules = [];
        $api = [];
        $reference = [];
        foreach ($docs as $doc) {
            $newfile = $doc->getFilename();
            $name = substr($doc->getBasename(), 0, -3);
            $contents = $doc->getContents();
            if (strpos($doc->getPathname(), 'docs'.DIRECTORY_SEPARATOR.'modules') !== false) {
                $newfile = 'docs/modules/' . $newfile;
                $modules[$name] = '/docs/modules/' . $doc->getBasename();
                $contents = str_replace('## ', '### ', $contents);
                $buttons = [
                    'source' => self::REPO_BLOB_URL."/".self::STABLE_BRANCH."/src/Codeception/Module/$name.php"
                ];
                // building version switcher
                foreach (['master', '2.2', '2.1', '2.0', '1.8'] as $branch) {
                    $buttons[$branch] = self::REPO_BLOB_URL."/$branch/docs/modules/$name.md";
                }
                $buttonHtml = "\n\n".'<div class="btn-group" role="group" style="float: right" aria-label="...">';
                foreach ($buttons as $link => $url) {
                    if ($link == self::STABLE_BRANCH) {
                        $link = "<strong>$link</strong>";
                    }
                    $buttonHtml.= '<a class="btn btn-default" href="'.$url.'">'.$link.'</a>';
                }
                $buttonHtml .= '</div>'."\n\n";
                $contents = $buttonHtml . $contents;
            } elseif (strpos($doc->getPathname(), 'docs'.DIRECTORY_SEPARATOR.'reference') !== false) {
                $newfile = 'docs/reference/' . $newfile;
                $reference[$name] = '/docs/reference/' . $doc->getBasename();
            } else {
                $newfile = 'docs/'.$newfile;
                $api[substr($name, 3)] = '/docs/'.$doc->getBasename();
            }

            copy($doc->getPathname(), 'package/site/' . $newfile);

            $highlight_languages = implode('|', ['php', 'html', 'bash', 'yaml', 'json', 'xml', 'sql']);
            $contents = preg_replace(
                "~```\s?($highlight_languages)\b(.*?)```~ms",
                "{% highlight $1 %}\n$2\n{% endhighlight %}",
                $contents
            );
            $contents = str_replace('{% highlight  %}', '{% highlight yaml %}', $contents);
            $contents = preg_replace("~```\s?(.*?)```~ms", "{% highlight yaml %}\n$1\n{% endhighlight %}", $contents);
            // set default language in order not to leave unparsed code inside '```'

            $matches = [];
            $title = $name;
            $contents = "---\nlayout: doc\ntitle: ".($title!="" ? $title." - " : "")
                ."Codeception - Documentation\n---\n\n".$contents;

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

            $this->taskWriteToFile('docs/'.$filename)
                ->text($doc)
                ->run();
        }


        $guides_list = '';
        foreach ($api as $name => $url) {
            $url = substr($url, 0, -3);
            $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $name);
            $name = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $name);
            $guides_list .= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        file_put_contents('_includes/guides.html', $guides_list);

        $this->say("Building Guides index");
        $this->taskWriteToFile('_includes/guides.html')
            ->text($guides_list)
            ->run();

        $this->taskWriteToFile('docs/index.html')
            ->line('---')
            ->line('layout: doc')
            ->line('title: Codeception Documentation')
            ->line('---')
            ->line('')
            ->line("<h1>Codeception Documentation Guides</h1>")
            ->line('')
            ->text($guides_list)
            ->run();

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
            if ($name == 'Commands') {
                continue;
            }
            if ($name == 'Configuration') {
                continue;
            }

            $url = substr($url, 0, -3);
            $reference_list .= '<li><a href="'.$url.'">'.$name.'</a></li>';
        }
        file_put_contents('_includes/reference.html', $reference_list);

        $this->say("Writing extensions docs");
        $this->taskWriteToFile('_includes/extensions.md')
            ->textFromFile(__DIR__.'/ext/README.md')
            ->run();

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
            $branch = implode('.', $branch);
        }
        $this->taskExec("git tag $version")->run();
        $this->taskExec("git push origin $branch --tags")->run();
    }

    protected function processChangelog()
    {
        $changelog = file_get_contents('CHANGELOG.md');

        //user
        $changelog = preg_replace('~\s@(\w+)~', ' **[$1](https://github.com/$1)**', $changelog);

        //issue
        $changelog = preg_replace(
            '~#(\d+)~',
            '[#$1](https://github.com/Codeception/Codeception/issues/$1)',
            $changelog
        );

        //module
        $changelog = preg_replace('~\s\[(\w+)\]\s~', ' **[$1]** ', $changelog);

        return $changelog;
    }

    /**
     * @desc cleans all log and temp directories
     */
    public function clean()
    {
        $this->taskCleanDir([
            'tests/log',
            'tests/data/claypit/tests/_output',
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

    /**
     * Publishes Codeception base
     * @param null $branch
     * @param null $tag
     */
    public function publishBase($branch = null, $tag = null)
    {
        if (!$branch) {
            $branch = self::STABLE_BRANCH;
        }

        $this->say("Updating Codeception Base distribution");

        $tempBranch = "tmp".uniqid();

        $this->taskGitStack()
            ->checkout("-b $tempBranch")
            ->run();

        $this->taskReplaceInFile('composer.json')
            ->from('"codeception/codeception"')
            ->to('"codeception/base"')
            ->run();

        $this->taskReplaceInFile('composer.json')
            ->regex('~^\s+"facebook\/webdriver".*$~m')
            ->to('')
            ->run();

        $this->taskReplaceInFile('composer.json')
            ->regex('~^\s+"guzzlehttp\/guzzle".*$~m')
            ->to('')
            ->run();

        $this->taskComposerUpdate()->run();
        $this->taskGitStack()
            ->add('composer.json')
            ->commit('auto-update')
            ->exec("push -f base $tempBranch:$branch")
            ->run();

        if ($tag) {
            $this->taskGitStack()
                ->exec("tag -d $tag")
                ->exec("push base :refs/tags/$tag")
                ->exec("tag $tag")
                ->push('base', $tag)
                ->run();
        }

        $this->taskGitStack()
            ->checkout('-- composer.json')
            ->checkout($branch)
            ->exec("branch -D $tempBranch")
            ->run();
    }

    /**
     * Checks Codeception code style
     * Most useful values for `report` option: `full`, `summary`, `diff`
     *
     * @param array $opt
     */
    public function codestyleCheck($opt = ['report|r' => 'summary'])
    {
        $this->say("Checking code style");

        $this->taskExec('php vendor/bin/phpcs')
            ->arg('.')
            ->arg('--standard=ruleset.xml')
            ->arg('--report=' . $opt['report'])
            ->arg('--ignore=tests/data,vendor')
            ->run();
    }

    public function codestyleFix()
    {
        $this->taskExec('php vendor/bin/phpcbf')
            ->arg('.')
            ->arg('--standard=ruleset.xml')
            ->arg('--ignore=tests/data,vendor')
            ->run();
    }
}
