<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\TestCase;

/**
 * Sequence solves data cleanup issue in alternative way.
 * Instead cleaning up the database between tests,
 * you can use generated unique names, that should not conflict.
 * When you create article on a site, for instance, you can assign it a unique name and then check it.
 *
 * This module has no actions, but introduces a function `sq` for generating unique sequences.
 *
 * ### Usage
 *
 * Function `sq` generates sequence, the only parameter it takes, is id.
 * You can get back to previously generated sequence using that id:
 *
 * ``` php
 * <?php
 * 'post'.sq(1); // post_521fbc63021eb
 * 'post'.sq(2); // post_521fbc6302266
 * 'post'.sq(1); // post_521fbc63021eb
 * ?>
 * ```
 *
 * Example:
 *
 * ``` php
 * <?php
 * $I->wantTo('create article');
 * $I->click('New Article');
 * $I->fillField('Title', 'Article'.sq('name'));
 * $I->fillField('Body', 'Demo article with Lorem Ipsum');
 * $I->click('save');
 * $I->see('Article'.sq('name') ,'#articles')
 * ?>
 * ```
 *
 * Populating Database:
 *
 * ``` php
 * <?php
 *
 * for ($i = 0; $i<10; $i++) {
 *      $I->haveInDatabase('users', array('login' => 'user'.sq($i), 'email' => 'user'.sq($i).'@email.com');
 * }
 * ?>
 * ```
 *
 */
class Sequence extends \Codeception\Module
{

    static $hash = [];

    public function _after(TestCase $t)
    {
        self::$hash = [];
    }
}

if (!function_exists('sq')) {
    require_once __DIR__ . '/../Util/sq.php';
} else {
    throw new ModuleException('Codeception\Module\Sequence', "function 'sq' already defiend");
}