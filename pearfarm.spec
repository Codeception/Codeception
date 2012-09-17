<?php
require_once __DIR__.'/autoload.php';
$version = \Codeception\Codecept::VERSION;

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('Codeception')
             ->setChannel('codeception.github.com/pear')
             ->setSummary('Full-stack PHP testing BDD framework.')
             ->setDescription('Codeception is new PHP full-stack testing framework. Inspired by BDD, it provides you absolutely new way for writing acceptance, functional and even unit tests. Powered by PHPUnit 3.6.')
             ->setReleaseVersion($version)
             ->setReleaseStability('stable')
             ->setApiVersion($version)
             ->setApiStability('stable')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Michael Bodnarchuk', 'DavertMik', 'davert@mail.ua')
             ->addGitFiles()
             ->addExcludeFilesRegex('~^tests\/*~')
             ->addExcludeFilesRegex('~^package\/*~')
             ->addExcludeFilesRegex('~^docs\/*~')
             ->addExcludeFilesRegex('~^bin\/*~')
             ->addFilesRegex('~^vendor.*php$~', Pearfarm_PackageSpec::ROLE_PHP)
             ->addFilesRegex('~^vendor.*js$~', Pearfarm_PackageSpec::ROLE_PHP)
             ->addFilesRegex('~^vendor.*dist$~', Pearfarm_PackageSpec::ROLE_PHP)
             ->addExecutable('codecept')
             ->addExecutable('codecept.bat')
             ;
