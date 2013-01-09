gu<?php
require_once __DIR__.'/../autoload.php';
$version = \Codeception\Codecept::VERSION;

chdir(__DIR__.'/../');

$docs = \Symfony\Component\Finder\Finder::create()->files('*.md')->sortByName()->in(__DIR__.'/../docs');

@mkdir("package/site");
system('git clone git@github.com:Codeception/codeception.github.com.git package/site/');
chdir('package/site');

$modules = array();
$api = array();
foreach ($docs as $doc) {
    $newfile = str_replace('.md','.markdown', $doc->getFilename());
    $name = str_replace('.markdown','', $newfile);
    $contents = file_get_contents($doc->getPathname());
    if (strpos($doc->getPathname(),'docs'.DIRECTORY_SEPARATOR.'modules')) {
        $newfile = 'docs/modules/'.$newfile;
        $url = str_replace('.md','', $doc->getFilename());
        $modules[$name] = '/docs/modules/'.$url;
        $contents = str_replace('# ','## ', $contents);
    } else {
        $newfile = 'docs/'.$newfile;
        $url = str_replace('.md','', $doc->getFilename());
        $api[substr($name,3)] = '/docs/'.$url;
    }

    copy($doc->getPathname(), $newfile);    

    $contents = preg_replace('~```\s?php(.*?)```~ms',"{% highlight php %}\n$1\n{% endhighlight %}", $contents);
    $contents = preg_replace('~```\s?html(.*?)```~ms',"{% highlight html %}\n$1\n{% endhighlight %}", $contents);
    $contents = preg_replace('~```(.*?)```~ms',"{% highlight yaml %}\n$1\n{% endhighlight %}", $contents);
    $contents = "---\nlayout: doc\ntitle: Codeception - Documentation\n---\n\n".$contents;

    file_put_contents($newfile, $contents);
}

$guides_list = '';
foreach ($api as $name => $url) {
    $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $name);
    $name = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $name);
    $guides_list.= '<li><a href="'.$url.'">'.$name.'</a></li>';
}

file_put_contents('_includes/guides.html', $guides_list);

$modules_list.= '';
foreach ($modules as $name => $url) {
    $modules_list.= '<li><a href="'.$url.'">'.$name.'</a></li>';
}

file_put_contents('_includes/modules.html', $modules_list);

system('git add .');
system('git commit -m="auto-updated documentation"');
system('git push');
chdir('..');
sleep(2);
@system('del /s /q /F site');
@system('rd /s /q site');
@system('rm -rf site');
echo "\n\nSITE BUILD SUCCESSFUL";
