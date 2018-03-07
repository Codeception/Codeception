<?php
namespace Codeception\Lib\Interfaces;

interface Db
{
    /**
     * Asserts that a row with the given column values exists.
     * Provide table name and column values.
     *
     * ```php
     * <?php
     * $I->seeInDatabase('users', ['name' => 'Davert', 'email' => 'davert@mail.com']);
     * ```
     * Fails if no such user found.
     *
     * Comparison expressions can be used as well:
     *
     * ```php
     * <?php
     * $I->seeInDatabase('posts', ['num_comments >=' => '0']);
     * $I->seeInDatabase('users', ['email like' => 'miles@davis.com']);
     * ```
     *
     * Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.
     *
     *
     * @param string $table
     * @param array $criteria
     */
    public function seeInDatabase($table, $criteria = []);

    /**
     * Effect is opposite to ->seeInDatabase
     *
     * Asserts that there is no record with the given column values in a database.
     * Provide table name and column values.
     *
     * ``` php
     * <?php
     * $I->dontSeeInDatabase('users', ['name' => 'Davert', 'email' => 'davert@mail.com']);
     * ```
     * Fails if such user was found.
     *
     * Comparison expressions can be used as well:
     *
     * ```php
     * <?php
     * $I->dontSeeInDatabase('posts', ['num_comments >=' => '0']);
     * $I->dontSeeInDatabase('users', ['email like' => 'miles%']);
     * ```
     *
     * Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.
     *
     * @param string $table
     * @param array $criteria
     */
    public function dontSeeInDatabase($table, $criteria = []);

    /**
     * Fetches a single column value from a database.
     * Provide table name, desired column and criteria.
     *
     * ``` php
     * <?php
     * $mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));
     * ```
     * Comparison expressions can be used as well:
     *
     * ```php
     * <?php
     * $post = $I->grabFromDatabase('posts', ['num_comments >=' => 100]);
     * $user = $I->grabFromDatabase('users', ['email like' => 'miles%']);
     * ```
     *
     * Supported operators: `<`, `>`, `>=`, `<=`, `!=`, `like`.
     *
     * @param string $table
     * @param string $column
     * @param array $criteria
     *
     * @return mixed
     */
    public function grabFromDatabase($table, $column, $criteria = []);
}
