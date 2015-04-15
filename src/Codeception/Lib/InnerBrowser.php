<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\MalformedLocator;
use Codeception\Exception\ModuleException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Interfaces\PageSourceSaver;
use Codeception\Lib\Interfaces\Web;
use Codeception\Module;
use Codeception\PHPUnit\Constraint\Crawler as CrawlerConstraint;
use Codeception\PHPUnit\Constraint\CrawlerNot as CrawlerNotConstraint;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;
use Codeception\TestCase;
use Codeception\Util\Locator;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Field\TextareaFormField;

class InnerBrowser extends Module implements Web, PageSourceSaver
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
        $this->_savePageSource(codecept_output_dir().str_replace(['::', '\\', '/'], ['.', '.', '.'], TestCase::getTestSignature($test)) . '.fail.html');
    }

    public function _after(TestCase $test)
    {
        $this->client = null;
        $this->crawler = null;
        $this->forms = [];
    }

    /**
     * @return Crawler
     * @throws ModuleException
     */
    private function getCrawler()
    {
        if (!$this->crawler) {
            throw new ModuleException($this, 'Crawler is null. Perhaps you forgot to call "amOnPage"?');
        }
        return $this->crawler;
    }

    private function getClient()
    {
        if ($this->client->getHistory()->isEmpty()) {
            throw new ModuleException($this, "Page not loaded. Use `\$I->amOnPage` to open it");
        }
        return $this->client;
    }

    public function _savePageSource($filename)
    {
        file_put_contents($filename, $this->getClient()->getInternalResponse()->getContent());
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
            $anchor = $this->getCrawler()->selectLink($link);
        }
        if (count($anchor)) {
            $this->crawler = $this->getClient()->click($anchor->first()->link());
            $this->forms = [];
            $this->debugResponse();
            return;
        }

        $buttonText = str_replace('"', "'", $link);
        $button = $this->crawler->selectButton($buttonText);
        if (count($button)) {
            $this->submitFormWithButton($button);
            $this->debugResponse();
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
            if ($tag == 'a') {
                $this->crawler = $this->getClient()->click($nodes->first()->link());
                $this->forms = [];
                $this->debugResponse();
                return;
            } elseif (
                ($tag == 'input' && in_array($type, ['submit', 'image'])) ||
                ($tag == 'button' && $type == 'submit')
            ) {
                $this->submitFormWithButton($nodes->first());
                $this->debugResponse();
                return;
            }
        }

    }

    protected function submitFormWithButton($button)
    {
        $form = $this->getFormFor($button);

        // Only now do we know which submit button was pressed.
        // Add it to the form object.
        $buttonNode = $button->getNode(0);
        if ($buttonNode->getAttribute("name")) {
            $f = new InputFormField($buttonNode);
            $form->set($f);
        }

        $this->debugSection('Uri', $form->getUri());
        $this->debugSection($form->getMethod(), $form->getValues());

        $this->crawler = $this->getClient()->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $form->getPhpFiles());
        $this->forms = [];
    }

    public function see($text, $selector = null)
    {
        if (!$selector) {
            $this->assertPageContains($text);
        } else {
            $nodes = $this->match($selector);
            $this->assertDomContains($nodes, $selector, $text);
        }
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) {
            $this->assertPageNotContains($text);
        } else {
            $nodes = $this->match($selector);
            $this->assertDomNotContains($nodes, $selector, $text);
        }
    }

    public function seeLink($text, $url = null)
    {
        $links = $this->getCrawler()->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('.//a[contains(@href, %s)]', Crawler::xpathLiteral($url)));
        }
        $this->assertDomContains($links, 'a');
    }

    public function dontSeeLink($text, $url = null)
    {
        $links = $this->getCrawler()->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('.//a[contains(@href, %s)]', Crawler::xpathLiteral($url)));
        }
        $this->assertDomNotContains($links, 'a');
    }

    /**
     * @return string
     * @throws ModuleException
     */
    public function _getCurrentUri()
    {
        $url = $this->getClient()->getHistory()->current()->getUri();
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
        $res = preg_match($uri, $this->_getCurrentUri(), $matches);
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
        $checkboxes = $this->getCrawler()->filter($checkbox);
        $this->assertDomContains($checkboxes->filter('input[checked=checked]'), 'checkbox');
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $checkboxes = $this->getCrawler()->filter($checkbox);
        \PHPUnit_Framework_Assert::assertEquals(0, $checkboxes->filter('input[checked=checked]')->count());
    }

    public function seeInField($field, $value)
    {
        $this->assert($this->proceedSeeInField($field, $value));
    }

    public function dontSeeInField($field, $value)
    {
        $this->assertNot($this->proceedSeeInField($field, $value));
    }

    protected function proceedSeeInField($field, $value)
    {
        $fields = $this->getFieldsByLabelOrCss($field);

        $currentValues = [];
        if ($fields->filter('textarea')->count() !== 0) {
            $currentValues = $fields->filter('textarea')->extract(['_text']);
        } elseif ($fields->filter('select')->count() !== 0) {
            $currentValues = $fields->filter('select option:selected')->extract(['value']);
        } elseif ($fields->filter('input[type=radio],input[type=checkbox]')->count() !== 0) {
            if (is_bool($value)) {
                $currentValues = [$fields->filter('input:checked')->count() > 0];
            } else {
                $currentValues = $fields->filter('input:checked')->extract(['value']);
            }
        } else {
            $currentValues = $fields->extract(['value']);
        }

        $strField = $field;
        if (is_array($field)) {
            $ident = reset($field);
            $strField = key($field) . '=>' . $ident;
        }

        return [
            'Contains',
            $value,
            $currentValues,
            "Failed testing for '$value' in $strField's value: " . implode(', ', $currentValues)
        ];
    }

    protected function getSubmissionFormFieldName($name)
    {
        if (substr($name, -2) === '[]') {
            return substr($name, 0, -2);
        }
        return $name;
    }

    public function submitForm($selector, $params, $button = null)
    {
        $form = $this->match($selector)->first();

        if (!count($form)) {
            throw new ElementNotFound($selector, 'Form');
        }

        $defaults = [];
        /** @var  \Symfony\Component\DomCrawler\Crawler|\DOMElement[] $fields */
        $fields = $form->filter('input:enabled,textarea:enabled,select:enabled,button:enabled,input[type=hidden]');
        foreach ($fields as $field) {
            $fieldName = $this->getSubmissionFormFieldName($field->getAttribute('name'));
            if (($field->getAttribute('type') === 'checkbox' || $field->getAttribute('type') === 'radio') && !$field->hasAttribute('checked')) {
                continue;
            } elseif ($field->getAttribute('type') === 'button') {
                continue;
            } elseif (($field->getAttribute('type') === 'submit' || $field->tagName === 'button') && $field->getAttribute('name') !== $button) {
                continue;
            } elseif ($field->tagName === 'select') {
                $values = [];
                $select = new Crawler($field);
                $options = $select->filter('option:enabled:selected');
                foreach ($options as $option) {
                    $values[] = $option->getAttribute('value');
                    if (!$field->hasAttribute('multiple')) {
                        break;
                    }
                }
                if (count($values) > 1) {
                    $defaults[$fieldName] = $values;
                } elseif (count($values) === 1) {
                    $defaults[$fieldName] = reset($values);
                }
                continue;
            } elseif (!empty($field->nodeValue)) {
                $defaults[$fieldName] = $field->nodeValue;
            }
            $defaults[$fieldName] = $field->getAttribute('value');
        }

        $requestParams = array_merge($defaults, $params);
        
        $method = $form->attr('method') ? $form->attr('method') : 'GET';
        $query = '';
        if (strtoupper($method) == 'GET') {
            $query = '?' . http_build_query($requestParams);
        }
        $this->debugSection('Uri', $this->getFormUrl($form));
        $this->debugSection('Method', $method);
        $this->debugSection('Parameters', $requestParams);

        $this->crawler = $this->client->request($method, $this->getFormUrl($form) . $query, $requestParams);
        $this->debugResponse();
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $form
     *
     * @return string
     */
    protected function getFormUrl($form)
    {
        $action = $form->attr('action');
        $currentUrl = $this->getClient()->getHistory()->current()->getUri();

        if (empty($action) || $action === '#') {
            return $currentUrl;
        }

        $build = parse_url($currentUrl);
        if ($build === false) {
            throw new TestRuntimeException("URL '$currentUrl' is malformed");
        }

        $uriParts = parse_url($action);
        if ($uriParts === false) {
            throw new TestRuntimeException("URI '$action' is malformed");
        }

        foreach ($uriParts as $part => $value) {
            if ($part === 'path' && strpos($value, '/') !== 0 && !empty($build[$part])) {
                // if it ends with a slash, relative paths are below it
                if (preg_match('~/$~', $build[$part])) {
                    $build[$part] = $build[$part] . $value;
                    continue;
                }
                // remove double slashes
                $dir = rtrim(dirname($build[$part]), '\\/');

                $build[$part] = $dir . '/' . $value;;
                continue;
            }
            $build[$part] = $value;
        }
        return \GuzzleHttp\Url::buildUrl($build);
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $node
     *
     * @return \Symfony\Component\DomCrawler\Form|\Symfony\Component\DomCrawler\Field\ChoiceFormField[]|\Symfony\Component\DomCrawler\Field\FileFormField[]
     */
    protected function getFormFor($node)
    {
        $form = $node->parents()->filter('form')->first();
        if (!$form) {
            $this->fail('The selected node does not have a form ancestor.');
        }
        $action = $this->getFormUrl($form);

        if (isset($this->forms[$action])) {
            return $this->forms[$action];
        }

        $formSubmits = $form->filter('*[type=submit]');

        // Inject a submit button if there isn't one.
        if ($formSubmits->count() == 0) {
            $autoSubmit = new \DOMElement('input');
            $form->rewind();
            $autoSubmit = $form->current()->appendChild($autoSubmit);
            $autoSubmit->setAttribute('type', 'submit'); // for forms with no submits
            $autoSubmit->setAttribute('name', 'codeception_added_auto_submit');
        }

        // Retrieve the store the Form object.
        $this->forms[$action] = $form->form();

        return $this->forms[$action];
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
        $fieldName = $field->attr('name');
        if ($field->attr('multiple')) {
            $fieldName = str_replace('[]', '', $fieldName);
        }

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
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($option));
        $name = $field->attr('name');
        // If the name is an array than we compare objects to find right checkbox
        $formField = $this->matchFormField($name, $form, new ChoiceFormField($field->getNode(0)));
        $formField->tick();
    }

    public function uncheckOption($option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($option));
        $name = $field->attr('name');
        // If the name is an array than we compare objects to find right checkbox
        $formField = $this->matchFormField($name, $form, new ChoiceFormField($field->getNode(0)));
        $formField->untick();

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
        $response = $this->getClient()->getInternalResponse();
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

        if (Locator::isCSS($selector)) {
            return $this->getCrawler()->filter($selector);
        }
        if (Locator::isXPath($selector)) {
            return $this->getCrawler()->filterXPath($selector);
        }
        throw new MalformedLocator($selector, 'XPath or CSS');
    }

    /**
     * @param array $by
     * @throws TestRuntimeException
     * @return Crawler
     */
    protected function strictMatch(array $by)
    {
        $type = key($by);
        $locator = $by[$type];
        switch ($type) {
            case 'id':
                return $this->filterByCSS("#$locator");
            case 'name':
                return $this->filterByXPath(sprintf('.//*[@name=%s]', Crawler::xpathLiteral($locator)));
            case 'css':
                return $this->filterByCSS($locator);
            case 'xpath':
                return $this->filterByXPath($locator);
            case 'link':
                return $this->filterByXPath(sprintf('.//a[.=%s]', Crawler::xpathLiteral($locator)));
            case 'class':
                return $this->filterByCSS(".$locator");
            default:
                throw new TestRuntimeException("Locator type '$by' is not defined. Use either: xpath, css, id, link, class, name");
        }
    }

    protected function filterByAttributes(Crawler $nodes, array $attributes)
    {
        foreach ($attributes as $attr => $val) {
            $nodes = $nodes->reduce(
                function (Crawler $node) use ($attr, $val) {
                    return $node->attr($attr) == $val;
                }
            );
        }
        return $nodes;

    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        if (@preg_match($cssOrXPathOrRegex, $this->client->getInternalResponse()->getContent(), $matches)) {
            return $matches[1];
        }
        $nodes = $this->match($cssOrXPathOrRegex);
        if ($nodes->count()) {
            return $nodes->first()->text();
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

    public function grabMultiple($cssOrXpath, $attribute = null)
    {
        $result = [];
        $nodes = $this->match($cssOrXpath);
        foreach ($nodes as $node) {
            if ($attribute) {
                $result[] = $node->attr($attribute);
                continue;
            }
            $result[] = $node->text();
        }
        return $result;
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
            $select = $nodes->filter('select');
            $is_multiple = $select->attr('multiple');
            $results = [];
            foreach ($select->children() as $option) {
                /** @var  \DOMElement $option */
                if ($option->getAttribute('selected') == 'selected') {
                    $val = $option->getAttribute('value');
                    if (!$is_multiple) {
                        return $val;
                    }
                    $results[] = $val;
                }
            }
            if (!$is_multiple) {
                return null;
            }
            return $results;
        }
        $this->fail("Element $field is not a form field or does not contain a form field");
    }

    public function setCookie($name, $val, array $params = [])
    {
        $cookies = $this->getClient()->getCookieJar();
        $params = array_merge($this->defaultCookieParameters, $params);
        extract($params);
        $cookies->set(new Cookie($name, $val, $expires, $path, $domain, $secure));
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
    }

    public function grabCookie($name, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
        $cookies = $this->getClient()->getCookieJar()->get($name, $params['path'], $params['domain']);
        if (!$cookies) {
            return null;
        }
        return $cookies->getValue();
    }

    public function seeCookie($name, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->getClient()->getCookieJar()->all());
        $this->assertNotNull($this->getClient()->getCookieJar()->get($name, $params['path'], $params['domain']));
    }

    public function dontSeeCookie($name, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->debugSection('Cookies', $this->getClient()->getCookieJar()->all());
        $this->assertNull($this->getClient()->getCookieJar()->get($name, $params['path'], $params['domain']));
    }

    public function resetCookie($name, array $params = [])
    {
        $params = array_merge($this->defaultCookieParameters, $params);
        $this->getClient()->getCookieJar()->expire($name, $params['path'], $params['domain']);
        $this->debugSection('Cookies', $this->client->getCookieJar()->all());
    }

    public function seeElement($selector, $attributes = [])
    {
        $nodes = $this->match($selector);
        if (!empty($attributes)) {
            $nodes = $this->filterByAttributes($nodes, $attributes);
            $selector .= "' with attribute(s) '" . trim(json_encode($attributes), '{}');
        }
        $this->assertDomContains($nodes, $selector);
    }

    public function dontSeeElement($selector, $attributes = [])
    {
        $nodes = $this->match($selector);
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
            list($floor, $ceil) = $expected;
            $this->assertTrue(
                $floor <= $counted && $ceil >= $counted,
                'Number of elements counted differs from expected range'
            );
        } else {
            $this->assertEquals(
                $expected, $counted,
                'Number of elements counted differs from expected number'
            );
        }
    }

    public function seeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
        $this->assertDomContains($selected, 'selected option');
        //If element is radio then we need to check value
        $value = $selected->getNode(0)->tagName == 'option' ? $selected->text() : $selected->getNode(0)->getAttribute('value');
        $this->assertEquals($optionText, $value);
    }

    public function dontSeeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
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
        $nodes = $this->getCrawler()->filter('title');
        if (!$nodes->count()) {
            throw new ElementNotFound("<title>", "Tag");
        }
        $this->assertContains($title, $nodes->first()->text(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $nodes = $this->getCrawler()->filter('title');
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
        $this->assertThat($this->getClient()->getInternalResponse()->getContent(), $constraint, $message);
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $constraint = new PageConstraint($needle, $this->_getCurrentUri());
        $this->assertThatItsNot($this->getClient()->getInternalResponse()->getContent(), $constraint, $message);
    }

    /**
     * @param $name
     * @param $form
     * @param $dynamicField
     * @return FileFormField
     */
    protected function matchFormField($name, $form, $dynamicField)
    {
        if (substr($name, -2) != '[]') {
            return $form[$name];
        }
        $name = substr($name, 0, -2);
        /** @var $item \Symfony\Component\DomCrawler\Field\FileFormField */
        foreach ($form[$name] as $item) {
            if ($item == $dynamicField) {
                return $item;
            }
        }
        return $item;
    }

    /**
     * @param $locator
     * @return Crawler
     */
    protected function filterByCSS($locator)
    {
        if (!Locator::isCSS($locator)) {
            throw new MalformedLocator($locator, 'css');
        }
        return $this->getCrawler()->filter($locator);
    }

    /**
     * @param $locator
     * @return Crawler
     */
    protected function filterByXPath($locator)
    {
        if (!Locator::isXPath($locator)) {
            throw new MalformedLocator($locator, 'xpath');
        }
        return $this->getCrawler()->filterXPath($locator);
    }
}
