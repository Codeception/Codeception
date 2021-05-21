<?php

namespace Codeception\Lib\Interfaces;

interface PageSourceSaver
{
    /**
     * Saves page source of to a file
     *
     * ```php
     * $this->getModule('{{MODULE_NAME}}')->_savePageSource(codecept_output_dir().'page.html');
     * ```
     * @api
     */
    public function _savePageSource(string $filename): void;

    /**
     * Use this method within an [interactive pause](https://codeception.com/docs/02-GettingStarted#Interactive-Pause) to save the HTML source code of the current page.
     *
     * ```php
     * <?php
     * $I->makeHtmlSnapshot('edit_page');
     * // saved to: tests/_output/debug/edit_page.html
     * $I->makeHtmlSnapshot();
     * // saved to: tests/_output/debug/2017-05-26_14-24-11_4b3403665fea6.html
     * ```
     */
    public function makeHtmlSnapshot(string $name = null): void;
}
