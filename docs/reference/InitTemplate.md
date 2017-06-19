
## Codeception\InitTemplate


* *Uses* `Codeception\Command\Shared\FileSystem`, `Codeception\Command\Shared\Style`

Codeception templates allow creating a customized setup and configuration for your project.
An abstract class for installation template. Each init template should extend it and implement a `setup` method.
Use it to build a custom setup class which can be started with `codecept init` command.


```php
<?php
namespace Codeception\Template; // it is important to use this namespace so codecept init could locate this template
class CustomInstall extends \Codeception\InitTemplate
{
     public function setup()
     {
        // implement this
     }
}
```
This class provides various helper methods for building customized setup


#### __construct()

 *public* __construct($input, $output) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L65)

#### addStyles()

 *public* addStyles($output) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Command/Shared/Style.php#L9)

#### ask()

 *protected* ask($question, $answer = null) 

```php
<?php
// propose firefox as default browser
$this->ask('select the browser of your choice', 'firefox');

// propose firefox or chrome possible options
$this->ask('select the browser of your choice', ['firefox', 'chrome']);

// ask true/false question
$this->ask('do you want to proceed (y/n)', true);
```

 * `param` $question
 * `param null` $answer
 * `return` mixed|string

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L107)

#### breakParts()

 *protected* breakParts($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Util/Shared/Namespaces.php#L6)

#### checkInstalled()

 *protected* checkInstalled($dir = null) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L208)

#### completeSuffix()

 *protected* completeSuffix($filename, $suffix) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Command/Shared/FileSystem.php#L25)

#### createActor()

 *protected* createActor($name, $directory, $suiteConfig) 

Create an Actor class and generate actions for it.
Requires a suite config as array in 3rd parameter.

 * `param` $name
 * `param` $directory
 * `param` $suiteConfig

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L223)

#### createDirectoryFor()

 *protected* createDirectoryFor($basePath, $className = null) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Command/Shared/FileSystem.php#L10)

#### createEmptyDirectory()

 *protected* createEmptyDirectory($dir) 

Create an empty directory and add a placeholder file into it
 * `param` $dir

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L195)

#### createFile()

 *protected* createFile($filename, $contents, $force = null, $flags = null) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Command/Shared/FileSystem.php#L46)

#### createHelper()

 *protected* createHelper($name, $directory) 

Create a helper class inside a directory

 * `param` $name
 * `param` $directory

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L174)

#### getNamespaceHeader()

 *protected* getNamespaceHeader($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Util/Shared/Namespaces.php#L31)

#### getNamespaceString()

 *protected* getNamespaceString($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Util/Shared/Namespaces.php#L25)

#### getNamespaces()

 *protected* getNamespaces($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Util/Shared/Namespaces.php#L40)

#### getShortClassName()

 *protected* getShortClassName($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Util/Shared/Namespaces.php#L19)

#### gitIgnore()

 *protected* gitIgnore($path) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L201)

#### initDir()

 *public* initDir($workDir) 

Change the directory where Codeception should be installed.

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L75)

#### removeSuffix()

 *protected* removeSuffix($classname, $suffix) 

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/Command/Shared/FileSystem.php#L40)

#### say()

 *protected* say($message = null) 

Print a message to console.

```php
<?php
$this->say('Welcome to Setup');
```


 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L136)

#### sayInfo()

 *protected* sayInfo($message) 

Print info message
 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L163)

#### saySuccess()

 *protected* saySuccess($message) 

Print a successful message
 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L145)

#### sayWarning()

 *protected* sayWarning($message) 

Print warning message
 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L154)

#### setup()

 *abstract public* setup() 

Override this class to create customized setup.
 * `return` mixed

[See source](https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php#L88)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.3/src/Codeception/InitTemplate.php">Help us to improve documentation. Edit module reference</a></div>
