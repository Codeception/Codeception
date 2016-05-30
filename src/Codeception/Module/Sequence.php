<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException;
use Codeception\TestInterface;

/**
 * Sequence solves data cleanup issue in alternative way.
 * Instead cleaning up the database between tests,
 * you can use generated unique names, that should not conflict.
 * When you create article on a site, for instance, you can assign it a unique name and then check it.
 *
 * This module has no actions, but introduces a function `sq` for generating unique sequences within test and
 * `sqs` for generating unique sequences across suite.
 *
 * ### Usage
 *
 * Function `sq` generates sequence, the only parameter it takes, is id.
 * You can get back to previously generated sequence using that id:
 *
 * ``` php
 * <?php
 * sq('post1'); // post1_521fbc63021eb
 * sq('post2'); // post2_521fbc6302266
 * sq('post1'); // post1_521fbc63021eb
 * ```
 *
 * Example:
 *
 * ``` php
 * <?php
 * $I->wantTo('create article');
 * $I->click('New Article');
 * $I->fillField('Title', sq('Article'));
 * $I->fillField('Body', 'Demo article with Lorem Ipsum');
 * $I->click('save');
 * $I->see(sq('Article') ,'#articles')
 * ```
 *
 * Populating Database:
 *
 * ``` php
 * <?php
 *
 * for ($i = 0; $i<10; $i++) {
 *      $I->haveInDatabase('users', array('login' => sq("user$i"), 'email' => sq("user$i").'@email.com');
 * }
 * ?>
 * ```
 *
 * Cest Suite tests:
 *
 * ``` php
 * <?php
 * class UserTest
 * {
 *     public function createUser(AcceptanceTester $I)
 *     {
 *         $I->createUser(sqs('user') . '@mailserver.com', sqs('login'), sqs('pwd'));
 *     }
 *
 *     public function checkEmail(AcceptanceTester $I)
 *     {
 *         $I->seeInEmailTo(sqs('user') . '@mailserver.com', sqs('login'));
 *     }
 *
 *     public function removeUser(AcceptanceTester $I)
 *     {
 *         $I->removeUser(sqs('user') . '@mailserver.com');
 *     }
 * }
 * ?>
 * ```
 *
 * ### Config
 *
 * By default produces unique string with param as a prefix:
 *
 * ```
 * sq('user') => 'user_876asd8as87a'
 * ```
 *
 * This behavior can be configured using `prefix` config param.
 *
 * Old style sequences:
 *
 * ```yaml
 * Sequence:
 *     prefix: '_'
 * ```
 *
 * Using id param inside prefix:
 *
 * ```yaml
 * Sequence:
 *     prefix: '{id}.'
 * ```
 */
class Sequence extends CodeceptionModule
{
    public static $hash = [];
    public static $suiteHash = [];
    public static $prefix = '';

    protected $config = ['prefix' => '{id}_'];

    public function _initialize()
    {
        static::$prefix = $this->config['prefix'];
    }

    public function _after(TestInterface $t)
    {
        self::$hash = [];
    }

    public function _afterSuite()
    {
        self::$suiteHash = [];
    }
}

if (!function_exists('sq') && !function_exists('sqs')) {
    require_once __DIR__ . '/../Util/sq.php';
} else {
    throw new ModuleException('Codeception\Module\Sequence', "function 'sq' and 'sqs' already defined");
}
