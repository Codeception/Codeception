<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

chdir(__DIR__.'/../');

$docs = \Symfony\Component\Finder\Finder::create()->files('*.md')->in(__DIR__.'/../docs');

@mkdir("package/site");
system('git clone git@github.com:Codeception/codeception.github.com.git package/site/');
chdir('package/site');

foreach ($docs as $doc) {
    if (strpos($doc->getPathname(),'modules')) {
        $newfile = 'docs/modules/'.$doc->getFilename();
    } else {
        $newfile = 'docs/'.$doc->getFilename();
    }

    $newfile = str_replace('.md','.markdown', $newfile);
    copy($doc->getPathname(), $newfile);
    $contents = file_get_contents($newfile);
    $contents = "---\nlayout: default\ntitle: Codeception - Documentation\n---\n\n".$contents;

    // add preg replace for code
    $contents = str_replace('``` php','{% highlight php %}', $contents);
    $contents = str_replace('```','{% endhighlight %}', $contents);

    file_put_contents($newfile, $contents);
}


system('git add .');
system('git commit -m="auto-updated documentation"');
system('git push');
chdir('..');
sleep(2);
@system('del /s /q /F site');
@system('rd /s /q site');
@system('rm -rf site');
echo "\n\nSITE BUILD SUCCESSFUL";
