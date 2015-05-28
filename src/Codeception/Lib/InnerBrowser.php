<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\TestRuntime;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;
use Codeception\PHPUnit\Constraint\Crawler as CrawlerConstraint;
use Codeception\PHPUnit\Constraint\CrawlerNot as CrawlerNotConstraint;
use Codeception\Module;
use Codeception\TestCase;
use Codeception\Util\Locator;
use Codeception\Lib\Interfaces\Web;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Field\TextareaFormField;
use DOMElement;

class InnerBrowser extends Module implements Web
{
    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @api
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client;

    /**
     * @var array|\Symfony\Component\DomCrawler\Form[]
     */
    protected $forms = [];

    protected $defaultCookieParameters = ['expires' => null, 'path' => '/', 'domain' => '', 'secure' => false];

    public function _failed(TestCase $test, $fail)
    {
        if (!$this->client || !$this->client->getInternalResponse()) {
            return;
        }
        $filename = str_replace(['::','\\','/'], ['.','.','.'], TestCase::getTestSignature($test)).'.fail.html';
        file_put_contents(codecept_output_dir($filename), $this->client->getInternalResponse()->getContent());
    }

    public function _after(TestCase $test)
    {
        $this->client  = null;
        $this->crawler = null;
        $this->forms   = [];
    }

    /**
     * Authenticates user for HTTP_AUTH
     *
     * @param $username
     * @param $password
     */
    public function amHttpAuthenticated($username, $password)
    {
        $this->client->setServerParameter('PHP_AUTH_USER', $username);
        $this->client->setServerParameter('PHP_AUTH_PW', $password);
    }

    public function amOnPage($page)
    {
        $this->crawler = $this->client->request('GET', $page);
        $this->forms = [];
        $this->debugResponse();
    }

    public function click($link, $context = null)
    {
        if ($context) {
            $this->crawler = $this->match($context);
        }

        if (is_array($link)) {
            $this->clickByLocator($link);
            return;
        }
        $anchor = $this->strictMatch(['link' => $link]);
        if (!count($anchor)) {
            $anchor = $this->crawler->selectLink($link);
        }
        if (count($anchor)) {
            $this->crawler = $this->client->click($anchor->first()->link());
            $this->forms = [];
            $this->debugResponse();
            return;
        }

        $buttonText = str_replace('"',"'", $link);
        $button = $this->crawler->selectButton($buttonText);
        if (count($button)) {
            $buttonValue = [];
            if (strval($button->attr('name')) !== '' && $button->attr('value') !== null) {
                $buttonValue = [$button->attr('name') => $button->attr('value')];
            }
            $this->proceedSubmitForm(
                $button->parents()->filter('form')->first(),
                $buttonValue
            );
            return;
        }

        $this->clickByLocator($link);
    }

    protected function clickByLocator($link)
    {
        $nodes = $this->match($link);
        if (!$nodes->count()) {
            throw new ElementNotFound($link, 'Link or Button by name or CSS or XPath');
        }

        foreach ($nodes as $node) {
            $tag = $node->nodeName;
            $type = $node->getAttribute('type');
            if ($tag === 'a') {
                $this->crawler = $this->client->click($nodes->first()->link());
                $this->forms = [];
                $this->debugResponse();
                break;
            } elseif (in_array($tag, ['input', 'button']) && in_array($type, ['submit', 'image'])) {
                $buttonValue = [];
                if (strval($nodes->first()->attr('name')) !== '' && $nodes->first()->attr('value') !== null) {
                    $buttonValue = [$nodes->first()->attr('name') => $nodes->first()->attr('value')];
                }
                $this->proceedSubmitForm(
                    $nodes->parents()->filter('form')->first(),
                    $buttonValue
                );
                break;
            }
        }
    }

    public function see($text, $selector = null)
    {
        if (!$selector) {
            $this->assertPageContains($text);
        } else {
            $nodes = $this->match($selector);
            $this->assertDomContains($nodes, $this->stringifySelector($selector), $text);
        }
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) {
            $this->assertPageNotContains($text);
        } else {
            $nodes = $this->match($selector);
            $this->assertDomNotContains($nodes, $this->stringifySelector($selector), $text);
        }
    }

    public function seeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('.//a[contains(@href, %s)]', Crawler::xpathLiteral($url)));
        }
        $this->assertDomContains($links, 'a');
    }

    public function dontSeeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('.//a[contains(@href, %s)]', Crawler::xpathLiteral($url)));
        }
        $this->assertDomNotContains($links, 'a');
    }

    public function _getCurrentUri()
    {
        $url   = $this->client->getHistory()->current()->getUri();
        $parts = parse_url($url);
        if (!$parts) {
            $this->fail("URL couldn't be parsed");
        }
        $uri = "";
        if (isset($parts['path'])) {
            $uri .= $parts['path'];
        }
        if (isset($parts['query'])) {
            $uri .= "?" . $parts['query'];
        }
        return $uri;
    }

    public function seeInCurrentUrl($uri)
    {
        $this->assertContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        $this->assertNotContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        $this->assertEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        $this->assertNotEquals($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertRegExp($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlMatches($uri)
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($uri, $this->_getCurrentUri());
    }

    public function grabFromCurrentUrl($uri = null)
    {
        if (!$uri) {
            return $this->_getCurrentUri();
        }
        $matches = [];
        $res     = preg_match($uri, $this->_getCurrentUri(), $matches);
        if (!$res) {
            $this->fail("Couldn't match $uri in " . $this->_getCurrentUri());
        }
        if (!isset($matches[1])) {
            $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        }
        return $matches[1];
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        $checkboxes = $this->crawler->filter($checkbox);
        $this->assertDomContains($checkboxes->filter('input[checked=checked]'), 'checkbox');
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $checkboxes = $this->crawler->filter($checkbox);
        \PHPUnit_Framework_Assert::assertEquals(0, $checkboxes->filter('input[checked=checked]')->count());
    }

    public function seeInField($field, $value)
    {
        $crawler = $this->getFieldsByLabelOrCss($field);
        $this->assert($this->proceedSeeInField($crawler, $value));
    }

    public function dontSeeInField($field, $value)
    {
        $crawler = $this->getFieldsByLabelOrCss($field);
        $this->assertNot($this->proceedSeeInField($crawler, $value));
    }
    
    public function seeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, false);
    }
    
    public function dontSeeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, true);
    }
    
    protected function proceedSeeInFormFields($formSelector, array $params, $assertNot)
    {
        $form = $this->match($formSelector)->first();
        if ($form->count() === 0) {
            throw new ElementNotFound($formSelector, 'Form');
        }
        foreach ($params as $name => $values) {
            $field = $form->filterXPath(sprintf('.//*[@name=%s]', Crawler::xpathLiteral($name)));
            if ($field->count() === 0) {
                throw new ElementNotFound(
                    sprintf('//*[@name=%s]', Crawler::xpathLiteral($name)),
                    'Form'
                );
            }
            if (!is_array($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $ret = $this->proceedSeeInField($field, $value);
                if ($assertNot) {
                    $this->assertNot($ret);
                } else {
                    $this->assert($ret);
                }
            }
        }
    }

    /**
     * Returns an array of values for the field with the passed name.  Usually
     * the array consists of a single value.  Used by proceedSeeInField
     * 
     * @param Form $form
     * @param string $fieldName
     * @return array
     */
    private function getFormFieldValues(Form $form, $fieldName)
    {
        $strField = $this->getSubmissionFormFieldName($fieldName);
        $values = [];
        if ($form->has($strField)) {
            $fields = $form->get($strField);
            // $form->get returns an array for fields with names ending in []
            if (!is_array($fields)) {
                $fields = [$fields];
            }
            foreach ($fields as $field) {
                if (!$field->hasValue()) {
                    continue;
                }
                // $field->getValue may return an array (multi-select for example) or a string value
                $values = array_merge($values, (array) $field->getValue());
            }
        }
        return $values;
    }
    
    protected function proceedSeeInField(Crawler $fields, $value)
    {
        $form = $this->getFormFor($fields);
        $fieldName = $fields->attr('name');
        $testValues = $this->getFormFieldValues($form, $fieldName);
        if (is_bool($value) && $value === true && !empty($testValues)) {
            $value = reset($testValues);
        } elseif (empty($testValues)) {
            $testValues = [''];
        }
        return [
            'Contains',
            $value,
            $testValues,
            "Failed testing for '$value' in $fieldName's value: " . var_export($testValues, true)
        ];
    }

    /**
     * Strips out one pair of trailing square brackets from a field's
     * name.
     *
     * @param string $name the field name
     * @return string the name after stripping trailing square brackets
     */
    protected function getSubmissionFormFieldName($name)
    {
        if (substr($name, -2) === '[]') {
            return substr($name, 0, -2);
        }
        return $name;
    }

    /**
     * Replaces boolean values in $params with the corresponding field's
     * value for checkbox form fields.
     *
     * The function loops over all input checkbox fields, checking if a
     * corresponding key is set in $params.  If it is, and the value is
     * boolean or an array containing booleans, the value(s) are
     * replaced in the array with the real value of the checkbox, and
     * the array is returned.
     *
     * @param Crawler $form the form to find checkbox elements
     * @param array $params the parameters to be submitted
     * @return array the $params array after replacing bool values
     */
    protected function setCheckboxBoolValues(Crawler $form, array $params)
    {
        $checkboxes = $form->filter('input[type=checkbox]');
        $chFoundByName = [];
        foreach ($checkboxes as $box) {
            $fieldName = $this->getSubmissionFormFieldName($box->getAttribute('name'));
            $pos = (!isset($chFoundByName[$fieldName])) ? 0 : $chFoundByName[$fieldName];
            $skip = (!isset($params[$fieldName]))
                || (!is_array($params[$fieldName]) && !is_bool($params[$fieldName]))
                || ($pos >= count($params[$fieldName])
                || (is_array($params[$fieldName]) && !is_bool($params[$fieldName][$pos])));
            if ($skip) {
                continue;
            }
            $values = $params[$fieldName];
            if ($values === true) {
                $params[$fieldName] = $box->getAttribute('value');
                $chFoundByName[$fieldName] = $pos + 1;
            } elseif ($values[$pos] === true) {
                $params[$fieldName][$pos] = $box->getAttribute('value');
                $chFoundByName[$fieldName] = $pos + 1;
            } elseif (is_array($values)) {
                array_splice($params[$fieldName], $pos, 1);
            } else {
                unset($params[$fieldName]);
            }
        }
        return $params;
    }

    /**
     * Submits the form currently selected in the passed Crawler, after
     * setting any values passed in $params and setting the value of the
     * passed button name.
     *
     * @param Crawler $frmCrawl the form to submit
     * @param array $params additional parameter values to set on the
     *        form
     * @param string $button the name of a submit button in the form
     */
    protected function proceedSubmitForm(Crawler $frmCrawl, array $params, $button = null)
    {
        $form = $this->getFormFor($frmCrawl);
        $defaults = $this->getFormValuesFor($form);
        $merged = array_merge($defaults, $params);
        $requestParams = $this->setCheckboxBoolValues($frmCrawl, $merged);

        if (!empty($button)) {
            $btnCrawl = $frmCrawl->filterXPath(sprintf('//*[not(@disabled) and @type="submit" and @name=%s]', Crawler::xpathLiteral($button)));
            if (count($btnCrawl)) {
                $requestParams[$button] = $btnCrawl->attr('value');
            }
        }

        $urlParts = parse_url($this->getFormUrl($frmCrawl));
        if ($urlParts === false) {
            $this->fail("Form url can't be parsed");
            return;
        }
        if (strcasecmp($form->getMethod(), 'GET') === 0) {
            $urlParts = $this->mergeUrls($urlParts, ['query' => http_build_query($requestParams)]);
        }
        $url = \GuzzleHttp\Url::buildUrl($urlParts);
        
        $this->debugSection('Uri', $url);
        $this->debugSection('Method', $form->getMethod());
        $this->debugSection('Parameters', $requestParams);

        $requestParams= $this->getFormPhpValues($requestParams);

        $this->crawler = $this->client->request(
            $form->getMethod(),
            $url,
            $requestParams,
            $form->getPhpFiles()
        );
        $this->debugResponse();
    }

    public function submitForm($selector, array $params, $button = null)
    {
        $form = $this->match($selector)->first();
        if (!count($form)) {
            throw new ElementNotFound($this->stringifySelector($selector), 'Form');
        }
        $this->proceedSubmitForm($form, $params, $button);
    }

    /**
     * Merges the passed $add argument onto $base.
     *
     * If a relative URL is passed as the 'path' part of the $add url
     * array, the relative URL is mapped using the base 'path' part as
     * its base.
     *
     * @param array $base the base URL
     * @param array $add the URL to merge
     * @return array the merged array
     */
    private function mergeUrls(array $base, array $add)
    {
        if (!empty($add['path']) && strpos($add['path'], '/') !== 0 && !empty($base['path'])) {
            // if it ends with a slash, relative paths are below it
            if (preg_match('~/$~', $base['path'])) {
                $add['path'] = $base['path'] . $add['path'];
            } else {
                // remove double slashes
                $dir = rtrim(dirname($base['path']), '\\/');
                $add['path'] = $dir . '/' . $add['path'];
            }
        }
        return array_merge($base, $add);
    }

    /**
     * Returns an absolute URL for the passed URI with the current URL
     * as the base path.
     *
     * @param string $uri the absolute or relative URI
     * @return string the absolute URL
     * @throws \Codeception\Exception\TestRuntime if either the current
     *         URL or the passed URI can't be parsed
     */
    protected function getAbsoluteUrlFor($uri)
    {
        $currentUrl = $this->client->getHistory()->current()->getUri();
        if (empty($uri) || $uri === '#') {
            return $currentUrl;
        }
        $build = parse_url($currentUrl);
        $uriParts = parse_url($uri);
        if ($build === false) {
            throw new TestRuntime("URL '$currentUrl' is malformed");
        } elseif ($uriParts === false) {
            throw new TestRuntime("URI '$uri' is malformed");
        }

        $abs = $this->mergeUrls($build, $uriParts);
        return \GuzzleHttp\Url::buildUrl($abs);
    }

    /**
     * Returns the form action's absolute URL.
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $form
     * @return string
     * @throws \Codeception\Exception\TestRuntime if either the current
     *         URL or the URI of the form's action can't be parsed
     */
    protected function getFormUrl(Crawler $form)
    {
        $action = $form->attr('action');
        return $this->getAbsoluteUrlFor($action);
    }

    /**
     * Returns a crawler Form object for the form pointed to by the
     * passed Crawler.
     *
     * The returned form is an independent Crawler created to take care
     * of the following issues currently experienced by Crawler's form
     * object:
     *  - input fields disabled at a higher level (e.g. by a surrounding
     *    fieldset) still return values
     *  - Codeception expects an empty value to match an unselected
     *    select box.
     *
     * The function clones the crawler's node and creates a new crawler
     * because it destroys or adds to the DOM for the form to achieve
     * the desired functionality.  Other functions simply querying the
     * DOM wouldn't expect them.
     *
     * @param Crawler $form the form
     * @param string $action the form's absolute URL action
     * @return Form
     */
    private function getFormFromCrawler(Crawler $form, $action)
    {
        $fakeDom = new \DOMDocument();
        $fakeDom->appendChild($fakeDom->importNode($form->getNode(0), true));
        $node = $fakeDom->documentElement;
        $cloned = new Crawler($node, $action);
        $shouldDisable = $cloned->filter('input:disabled:not([disabled]),select option:disabled,select optgroup:disabled option:not([disabled])');
        foreach ($shouldDisable as $field) {
            $field->parentNode->removeChild($field);
        }
        $selectNonMulti = $cloned->filterXPath('//select[not(@multiple) and not(option[@value=""])]');
        $opt = new DOMElement('option');
        foreach ($selectNonMulti as $field) {
            $node = $field->insertBefore($opt, $field->firstChild);
            $node->setAttribute('value', '');
        }
        return $cloned->form();
    }

    /**
     * Returns the DomCrawler\Form object for the form pointed to by
     * $node or its closes form parent.
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $node
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function getFormFor(Crawler $node)
    {
        if (strcasecmp($node->first()->getNode(0)->tagName, 'form') === 0) {
            $form = $node->first();
        } else {
            $form = $node->parents()->filter('form')->first();
        }
        if (!$form) {
            $this->fail('The selected node is not a form and does not have a form ancestor.');
        }
        $action = $this->getFormUrl($form);
        if (!isset($this->forms[$action])) {
            $this->forms[$action] = $this->getFormFromCrawler($form, $action);
        }
        return $this->forms[$action];
    }
    
    /**
     * Returns an array of name => value pairs for the passed form.
     *
     * The function calls getPhpValues on the passed form object, then
     * resets numeric array indexes for array field names without array
     * keys specified (i.e. 'fieldname[]' but not 'fieldname[keyname]')
     * as expected by setCheckboxBoolValues.
     *
     * @param \Symfony\Component\DomCrawler\Form the form
     * @return array an array of name => value pairs
     */
    protected function getFormValuesFor(Form $form)
    {
        $values = $form->getPhpValues();
        $fields = $form->all();
        foreach ($fields as $field) {
            $name = $this->getSubmissionFormFieldName($field->getName());
            if (!empty($values[$name]) && substr($field->getName(), -2) === '[]') {
                $values[$name] = array_values($values[$name]);
            }
        }
        return $values;
    }

    public function fillField($field, $value)
    {
        $input = $this->getFieldByLabelOrCss($field);
        $form = $this->getFormFor($input);
        $name = $input->attr('name');

        $dynamicField = $input->getNode(0)->tagName == 'textarea'
            ? new TextareaFormField($input->getNode(0))
            : new InputFormField($input->getNode(0));
        $formField = $this->matchFormField($name, $form, $dynamicField);
        $formField->setValue($value);
    }

    /**
     * @param $field
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getFieldsByLabelOrCss($field)
    {
        if (is_array($field)) {
            $input = $this->strictMatch($field);
            if (!count($input)) {
                throw new ElementNotFound($field);
            }
            return $input;
        }

        // by label
        $label = $this->strictMatch(['xpath' => sprintf('.//label[text()=%s]', Crawler::xpathLiteral($field))]);
        if (count($label)) {
            $label = $label->first();
            if ($label->attr('for')) {
                $input = $this->strictMatch(['id' => $label->attr('for')]);
            }
        }

        // by name
        if (!isset($input)) {
            $input = $this->strictMatch(['name' => $field]);
        }

        // by CSS and XPath
        if (!count($input)) {
            $input = $this->match($field);
        }

        if (!count($input)) {
            throw new ElementNotFound($field, 'Form field by Label or CSS');
        }

        return $input;
    }

    protected function getFieldByLabelOrCss($field)
    {
        $input = $this->getFieldsByLabelOrCss($field);
        return $input->first();
    }

    public function selectOption($select, $option)
    {
        $field = $this->getFieldByLabelOrCss($select);
        $form = $this->getFormFor($field);
        $fieldName = $this->getSubmissionFormFieldName($field->attr('name'));

        if (is_array($option)) {
            $options = [];
            foreach ($option as $opt) {
                $options[] = $this->matchOption($field, $opt);
            }
            $form[$fieldName]->select($options);
            return;
        }

        $form[$fieldName]->select($this->matchOption($field, $option));
    }

    protected function matchOption(Crawler $field, $option)
    {
        $options = $field->filterXPath(sprintf('//option[text()=normalize-space("%s")]', $option));
        if ($options->count()) {
            if ($options->first()->attr('value')) {
                return $options->first()->attr('value');
            }
            return $options->first()->text();
        }
        return $option;
    }

    public function checkOption($option)
    {
        $this->proceedCheckOption($option)->tick();
    }

    public function uncheckOption($option)
    {
        $this->proceedCheckOption($option)->untick();
    }

    /**
     * @param $option
     * @return ChoiceFormField
     */
    protected function proceedCheckOption($option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($option));
        $name = $field->attr('name');

        if ($field->getNode(0) === null) {
            throw new TestRuntime("Form field $name is not located");
        }
        // If the name is an array than we compare objects to find right checkbox
        $formField = $this->matchFormField($name, $form, new ChoiceFormField($field->getNode(0)));
        if (!$formField instanceof ChoiceFormField) {
            throw new TestRuntime("Form field $name is not a checkable");
        }
        return $formField;
    }

    public function attachFile($field, $filename)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($field));
        $path = Configuration::dataDir() . $filename;
        $name = $field->attr('name');
        if (!is_readable($path)) {
            $this->fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        }
        $formField = $this->matchFormField($name, $form, new FileFormField($field->getNode(0)));
        if (is_array($formField)) {
            $this->fail("Field $name is ignored on upload, field $name is treated as array.");
        }

        $formField->upload($path);
    }

    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends a GET ajax request with specified params.
     *
     * See ->sendAjaxPostRequest for examples.
     *
     * @param $uri
     * @param $params
     */
    public function sendAjaxGetRequest($uri, $params = [])
    {
        $this->sendAjaxRequest('GET', $uri, $params);
    }

    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends a POST ajax request with specified params.
     * Additional params can be passed as array.
     *
     * Example:
     *
     * Imagine that by clicking checkbox you trigger ajax request which updates user settings.
     * We emulate that click by running this ajax request manually.
     *
     * ``` php
     * <?php
     * $I->sendAjaxPostRequest('/updateSettings', array('notifications' => true)); // POST
     * $I->sendAjaxGetRequest('/updateSettings', array('notifications' => true)); // GET
     *
     * ```
     *
     * @param $uri
     * @param $params
     */
    public function sendAjaxPostRequest($uri, $params = [])
    {
        $this->sendAjaxRequest('POST', $uri, $params);
    }

    /**
     * If your page triggers an ajax request, you can perform it manually.
     * This action sends an ajax request with specified method and params.
     *
     * Example:
     *
     * You need to perform an ajax request specifying the HTTP method.
     *
     * ``` php
     * <?php
     * $I->sendAjaxRequest('PUT', '/posts/7', array('title' => 'new title'));
     *
     * ```
     *
     * @param $method
     * @param $uri
     * @param $params
     */
    public function sendAjaxRequest($method, $uri, $params = [])
    {
        $this->client->request($method, $uri, $params, [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $this->debugSection('Response', $this->getResponseStatusCode());
        $this->debugSection('Page', $this->client->getHistory()->current()->getUri());
        $this->debugSection('Cookies', $this->client->getInternalRequest()->getCookies());
        $this->debugSection('Headers', $this->client->getInternalResponse()->getHeaders());
    }

    protected function getResponseStatusCode()
    {
        // depending on Symfony version
        $response = $this->client->getInternalResponse();
        if (method_exists($response, 'getStatus')) {
            return $response->getStatus();
        }
        if (method_exists($response, 'getStatusCode')) {
            return $response->getStatusCode();
        }
        return "N/A";
    }

    /**
     * @param $selector
     *
     * @return Crawler
     */
    protected function match($selector)
    {
        if (is_array($selector)) {
            return $this->strictMatch($selector);
        }
        try {
            $selector = CssSelector::toXPath($selector);
        } catch (ParseException $e) {
        }
        if (!Locator::isXPath($selector)) {
            return new \Symfony\Component\DomCrawler\Crawler;
        }

        return @$this->crawler->filterXPath($selector);
    }

    /**
     * @param array $by
     * @throws TestRuntime
     * @return Crawler
     */
    protected function strictMatch(array $by)
    {
        if (!$this->crawler) {
            throw new TestRuntime('Crawler is null. Perhaps you forgot to call "amOnPage"?');
        }

        $type = key($by);
        $locator = $by[$type];
        switch ($type) {
            case 'id':
                return $this->crawler->filter("#$locator");
            case 'name':
                return @$this->crawler->filterXPath(sprintf('.//*[@name=%s]', Crawler::xpathLiteral($locator)));
            case 'css':
                return $this->crawler->filter($locator);
            case 'xpath':
                return @$this->crawler->filterXPath($locator);
            case 'link':
                return @$this->crawler->filterXPath(sprintf('.//a[.=%s]', Crawler::xpathLiteral($locator)));
            case 'class':
                return $this->crawler->filter(".$locator");
            default:
                throw new TestRuntime("Locator type '$by' is not defined. Use either: xpath, css, id, link, class, name");
        }
    }

    protected function filterByAttributes(Crawler $nodes, array $attributes)
    {
        foreach ($attributes as $attr => $val) {
            $nodes = $nodes->reduce(function(Crawler $node) use ($attr, $val) {
                return $node->attr($attr) == $val;
            });
        }
        return $nodes;

    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $nodes = $this->match($cssOrXPathOrRegex);
        if ($nodes->count()) {
            return $nodes->first()->text();
        }
        if (@preg_match($cssOrXPathOrRegex, $this->client->getInternalResponse()->getContent(), $matches)) {
            return $matches[1];
        }
        throw new ElementNotFound($cssOrXPathOrRegex, 'Element that matches CSS or XPath or Regex');
    }

    public function grabAttributeFrom($cssOrXpath, $attribute)
    {
        $nodes = $this->match($cssOrXpath);
        if (!$nodes->count()) {
            throw new ElementNotFound($cssOrXpath, 'Element that matches CSS or XPath');
        }
        return $nodes->first()->attr($attribute);
    }

    /**
     * @param $field
     *
     * @return array|mixed|null|string
     */
    public function grabValueFrom($field)
    {
        $nodes = $this->match($field);
        if (!$nodes->count()) {
            throw new ElementNotFound($field, 'Field');
        }

        if ($nodes->filter('textarea')->count()) {
            return $nodes->filter('textarea')->text();
        }
        if ($nodes->filter('input')->count()) {
            return $nodes->filter('input')->attr('value');
        }

        if ($nodes->filter('select')->count()) {
            /** @var  \Symfony\Component\DomCrawler\Crawler $select */
            $select      = $nodes->filter('select');
            $isMultiple  = (bool)$select->attr('multiple');
            $results     = [];
            foreach ($select->children() as $option) {
                /** @var  \DOMElement $option */
                if ($option->getAttribute('selected') == 'selected') {
                    $val = $option->getAttribute('value');
                    if (!$isMultiple) {
                        return $val;
                    }
                    $results[] = $val;
                }
            }
            if (!$isMultiple) {
                return null;
            }
            return $results;
        }
        $this->fail("Element $field is not a form field or does not contain a form field");
    }

    public function setCookie($name, $val, array $params = [])
    {
        $cookies = $this->client->getCookieJar();
        $params = array_merge($this->defaultCookieParameters, $params);

        $expires      = isset($params['expires']) ? $params['expires'] : null;
        $path         = isset($params['path'])    ? $params['path'] : null;
        $domain       = isset($params['domain'])  ? $params['domain'] : '';
        $secure       = isset($params['secure'])  ? $params['secure'] : false;
        $httpOnly     = isset($params['httpOnly'])  ? $params['httpOnly'] : true;
        $encodedValue = isset($params['encodedValue'])  ? $params['encodedValue'] : false;

        $cookies->set(new Cookie($name, $val, $expires, $path, $domain, $secure, $httpOnly, $encodedValue));
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
    }

    public function grabCookie($cookie, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
        $cookies = $this->client->getCookieJar()->get($cookie, $params['path'], $params['domain']);
        if (!$cookies) {
            return null;
        }
        return $cookies->getValue();
    }

    public function seeCookie($cookie, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
        $this->assertNotNull($this->client->getCookieJar()->get($cookie, $params['path'], $params['domain']));
    }

    public function dontSeeCookie($cookie, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
        $this->assertNull($this->client->getCookieJar()->get($cookie, $params['path'], $params['domain']));
    }

    public function resetCookie($name, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->client->getCookieJar()->expire($name, $params['path'], $params['domain']);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
    }

    private function stringifySelector($selector)
    {
        if (is_array($selector)) {
            return trim(json_encode($selector), '{}');
        }
        return $selector;
    }

    public function seeElement($selector, $attributes = [])
    {
        $nodes = $this->match($selector);
        $selector = $this->stringifySelector($selector);
        if (!empty($attributes)) {
            $nodes = $this->filterByAttributes($nodes, $attributes);
            $selector .= "' with attribute(s) '" . trim(json_encode($attributes), '{}');
        }
        $this->assertDomContains($nodes, $selector);
    }

    public function dontSeeElement($selector, $attributes = [])
    {
        $nodes = $this->match($selector);
        $selector = $this->stringifySelector($selector);
        if (!empty($attributes)) {
            $nodes = $this->filterByAttributes($nodes, $attributes);
            $selector .= "' with attribute(s) '" . trim(json_encode($attributes), '{}');
        }
        $this->assertDomNotContains($nodes, $selector);
    }
    
    public function seeNumberOfElements($selector, $expected)
    {
        $counted = count($this->match($selector));
        if (is_array($expected)) {
            list($floor,$ceil) = $expected;
            $this->assertTrue($floor<=$counted &&  $ceil>=$counted,
                    'Number of elements counted differs from expected range' );
        } else {
            $this->assertEquals($expected, $counted,
                    'Number of elements counted differs from expected number' );
        }
    }    

    public function seeOptionIsSelected($selector, $optionText)
    {
        $selected = $this->matchSelectedOption($selector);
        $this->assertDomContains($selected, 'selected option');
        //If element is radio then we need to check value
        $value = $selected->getNode(0)->tagName == 'option' ? $selected->text() : $selected->getNode(0)->getAttribute('value');
        $this->assertEquals($optionText, $value);
    }

    public function dontSeeOptionIsSelected($selector, $optionText)
    {
        $selected = $this->matchSelectedOption($selector);
        if (!$selected->count()) {
            $this->assertEquals(0, $selected->count());
            return;
        }
         //If element is radio then we need to check value
        $value = $selected->getNode(0)->tagName == 'option' ? $selected->text() : $selected->getNode(0)->getAttribute('value');
        $this->assertNotEquals($optionText, $value);
    }

    protected function matchSelectedOption($select)
    {
        $nodes = $this->getFieldsByLabelOrCss($select);
        return $nodes->filter('option[selected],input:checked');
    }

    /**
     * Asserts that current page has 404 response status code.
     */
    public function seePageNotFound()
    {
        $this->seeResponseCodeIs(404);
    }

    /**
     * Checks that response code is equal to value provided.
     *
     * @param $code
     *
     * @return mixed
     */
    public function seeResponseCodeIs($code)
    {
        $this->assertEquals($code, $this->getResponseStatusCode());
    }

    public function seeInTitle($title)
    {
        $nodes = $this->crawler->filter('title');
        if (!$nodes->count()) {
            throw new ElementNotFound("<title>","Tag");
        }
        $this->assertContains($title, $nodes->first()->text(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $nodes = $this->crawler->filter('title');
        if (!$nodes->count()) {
            $this->assertTrue(true);
            return;
        }
        $this->assertNotContains($title, $nodes->first()->text(), "page title contains $title");
    }

    protected function assertDomContains($nodes, $message, $text = '')
    {
        $constraint = new CrawlerConstraint($text, $this->_getCurrentUri());
        $this->assertThat($nodes, $constraint, $message);
    }

    protected function assertDomNotContains($nodes, $message, $text = '')
    {
        $constraint = new CrawlerNotConstraint($text, $this->_getCurrentUri());
        $this->assertThat($nodes, $constraint, $message);
    }

    protected function assertPageContains($needle, $message = '')
    {
        $constraint = new PageConstraint($needle, $this->_getCurrentUri());
        $this->assertThat(
            htmlspecialchars_decode(strip_tags($this->client->getInternalResponse()->getContent())),
            $constraint,
            $message
        );
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $constraint = new PageConstraint($needle, $this->_getCurrentUri());
        $this->assertThatItsNot(
            htmlspecialchars_decode(strip_tags($this->client->getInternalResponse()->getContent())),
            $constraint,
            $message
        );
    }

    /**
     * @param $name
     * @param $form
     * @param $dynamicField
     * @return FormField
     */
    protected function matchFormField($name, $form, $dynamicField)
    {
        if (substr($name, -2) != '[]') {
            return $form[$name];
        }
        $name = substr($name, 0, -2);
        /** @var $item \Symfony\Component\DomCrawler\Field\FormField */
        foreach ($form[$name] as $item) {
            if ($item == $dynamicField) {
                return $item;
            }
        }
        throw new TestRuntime("None of form fields by {$name}[] were not matched");
    }

    /**
     * @param $requestParams
     * @return array
     */
    protected function getFormPhpValues($requestParams)
    {
        foreach ($requestParams as $name => $value) {
            $qs = http_build_query([$name => $value], '', '&');
            if (!empty($qs)) {
                parse_str($qs, $expandedValue);
                $varName = substr($name, 0, strlen(key($expandedValue)));
                $requestParams = array_replace_recursive($requestParams, [$varName => current($expandedValue)]);
            }
        }
        return $requestParams;
    }
}
