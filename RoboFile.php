<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    // define public methods as commands
    public function prepare()
    {
        $config = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

        $config['name'] = 'codeception/phpunit-wrapper-test';
        $config['require-dev']['codeception/codeception'] = getenv('CODECEPTION_VERSION');
        $config['replace'] = ['codeception/phpunit-wrapper' => '*'];

        file_put_contents(__DIR__ . '/composer.json', json_encode($config));
    }

    public function test($params)
    {
        return $this->taskExec(__DIR__ . '/vendor/bin/codecept run ' . $params)
            ->dir(__DIR__ .'/vendor/codeception/codeception')
            ->run();
    }
}