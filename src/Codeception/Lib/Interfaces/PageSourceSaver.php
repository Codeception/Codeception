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
     * @param $filename
     */
    public function _savePageSource($filename);

    /**
     * Saves current page's HTML into a temprary file.
     * Use this method in debug mode within an interactive pause to get a source code of current page.
     *
     * ```php
     * <?php
     * $I->makeHtmlSnapshot('edit_page');
     * // saved to: tests/_output/debug/edit_page.html
     * $I->makeHtmlSnapshot();
     * // saved to: tests/_output/debug/2017-05-26_14-24-11_4b3403665fea6.html
     * ```
     *
     * @param null $name
     */
    public function makeHtmlSnapshot($name = null);
}
