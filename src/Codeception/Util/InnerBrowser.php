<?php
namespace Codeception\Util;
use Codeception\Exception\ContentNotFound;
use Codeception\Exception\ElementNotFound;
use Codeception\PHPUnit\Constraint\CrawlerNot;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\DomCrawler\Crawler;


class InnerBrowser extends \Codeception\Module implements FrameworkInterface {


    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @api
     * @var \Symfony\Component\BrowserKit\Client
     */
    public $client;

    protected $forms = array();

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        if (!$this->client || !$this->client->getInternalResponse()) return;
        file_put_contents(\Codeception\Configuration::logDir() . basename($test->getFileName()) . '.page.debug.html', $this->client->getInternalResponse()->getContent());
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->client = null;
        $this->crawler = null;
        $this->forms = array();

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
        $this->debugResponse();
    }

    public function click($link, $context = null)
    {
        $literal = Crawler::xpathLiteral($link);

        if ($context) {
            $this->crawler = $this->match($context);
        }

        $anchor = $this->crawler->filterXPath('.//a[.=' . $literal . ']');
        if (!count($anchor)) $anchor = $this->crawler->selectLink($link);
        if (count($anchor)) {
            $this->crawler = $this->client->click($anchor->first()->link());
            $this->debugResponse();
            return;
        }

        $button = $this->crawler->selectButton($link);
        if (count($button)) {
            $this->submitFormWithButton($button);
            $this->debugResponse();
            return;
        }

        $nodes = $this->match($link);

        if (!$nodes->count()) throw new ElementNotFound($link, 'Link or Button by name or CSS or XPath');
        foreach ($nodes as $node) {
            if ($node->nodeName == 'a') {
                $this->crawler = $this->client->click($nodes->first()->link());
                $this->debugResponse();
                return;
            } elseif($node->nodeName == 'input' && $node->getAttribute('type') == 'submit') {
                $this->submitFormWithButton($nodes->first());
                $this->debugResponse();
                return;
            }
        }
    }

    protected function submitFormWithButton($button)
    {
        foreach ($button as $node) {
            if (!$node->getAttribute('name')) {
                $node->setAttribute('name','codeception_generated_button_name');
            }
        }
        $domForm = $button->form();
        $form = $this->getFormFor($button);

        $this->debugSection('Uri', $domForm->getUri());
        $this->debugSection($domForm->getMethod(), json_encode($form->getValues()));

        $this->crawler = $this->client->request($domForm->getMethod(), $domForm->getUri(), $form->getPhpValues(), $form->getPhpFiles());
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
        $links = $this->crawler->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('descendant-or-self::a[contains(@href, %s)]', Crawler::xpathLiteral($this->escape($url))));
        }
        $this->assertDomContains($links, 'a');
    }

    public function dontSeeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($text);
        if ($url) {
            $links = $links->filterXPath(sprintf('descendant-or-self::a[contains(@href, %s)]', Crawler::xpathLiteral($this->escape($url))));
        }
        $this->assertDomNotContains($links, 'a');
    }

    public function _getCurrentUri()
    {
        $url = $this->client->getHistory()->current()->getUri();
        $parts = parse_url($url);
        if (!$parts) $this->fail("URL couldn't be parsed");
        $uri = "";
        if (isset($parts['path'])) $uri .= $parts['path'];
        if (isset($parts['query'])) $uri .= "?".$parts['query'];
        return $uri;
    }

    public function seeInCurrentUrl($uri)
    {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        \PHPUnit_Framework_Assert::assertNotContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        \PHPUnit_Framework_Assert::assertEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        \PHPUnit_Framework_Assert::assertNotEquals($uri, $this->_getCurrentUri());
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
        if (!$uri) return $this->_getCurrentUri();
        $matches = array();
        $res = preg_match($uri, $this->_getCurrentUri(), $matches);
        if (!$res) $this->fail("Couldn't match $uri in ".$this->_getCurrentUri());
        if (!isset($matches[1])) $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        return $matches[1];
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        $checkboxes = $this->crawler->filter($checkbox);
        $this->assertDomContains($checkboxes->filter('input[checked=checked]'),'checkbox');
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $checkboxes = $this->crawler->filter($checkbox);
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
        $field = $this->getFieldByLabelOrCss($field);
        if (empty($field)) {
            throw new ElementNotFound('Input field');
        }
        $currentValue = $field->filter('textarea')->extract(array('_text'));
        if (!$currentValue) {
            $currentValue = $field->extract(array('value'));
        }
        return array('Contains', $this->escape($value), $currentValue);
    }

    public function submitForm($selector, $params)
    {
        $form = $this->match($selector)->first();

        if (!count($form)) {
            throw new ElementNotFound($selector, 'Form');
        }

        $url = '';
        $fields = $form->filter('input');
        foreach ($fields as $field) {
            if ($field->getAttribute('type') == 'checkbox') continue;
            if ($field->getAttribute('type') == 'radio') continue;
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->getAttribute('value')) . '&';
        }

        $fields = $form->filter('textarea');
        foreach ($fields as $field) {
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->nodeValue) . '&';
        }

        $fields = $form->filter('select');
        foreach ($fields as $field) {
            foreach ($field->childNodes as $option) {
                if ($option->getAttribute('selected') == 'selected')
                    $url .= sprintf('%s=%s', $field->getAttribute('name'), $option->getAttribute('value')) . '&';
            }
        }

        $url .= http_build_query($params);
        parse_str($url, $params);
        $method = $form->attr('method') ? $form->attr('method') : 'GET';
        $query = '';
        if (strtoupper($method) == 'GET') {
            $query = '?'.http_build_query($params);
        }
        $this->debugSection('Uri', $this->getFormUrl($form));
        $this->debugSection('Method', $method);
        $this->debugSection('Parameters', json_encode($params));

        $this->crawler = $this->client->request($method, $this->getFormUrl($form).$query, $params);
        $this->debugResponse();
    }

    protected function getFormUrl($form)
    {
        $action = $form->attr('action');
        if ((!$action) or ($action == '#')) $action = $this->client->getHistory()->current()->getUri();
        return $action;
    }

    protected function getFormFor($node)
    {
        $form = $node->parents()->filter('form')->first();
        if (!$form) $this->fail('The selected node does not have a form ancestor.');
        $action = $this->getFormUrl($form);

        if (!isset($this->forms[$action])) {
            $submit = new \DOMElement('input');
            $submit = $form->current()->appendChild($submit);
            $submit->setAttribute('type','submit'); // for forms with no submits
            $submit->setAttribute('name','codeception_added_auto_submit');

            // Symfony2.1 DOM component requires name for each field.
            if (!$form->filter('input[type=submit]')->attr('name')) {
                $form = $form->filter('input[type=submit][name=codeception_added_auto_submit]')->form();
            } else {
                $form = $form->filter('input[type=submit]')->form();
            }
            $this->forms[$action] = $form;
        }
        return $this->forms[$action];
    }

    public function fillField($field, $value)
    {
        $input = $this->getFieldByLabelOrCss($field);
        $form = $this->getFormFor($input);
        $form[$input->attr('name')] = $value;
    }

    protected function getFieldByLabelOrCss($field)
    {
        $label = $this->match(sprintf('descendant-or-self::label[text()="%s"]', $field));
        if (count($label)) {
            $label = $label->first();
            if ($label->attr('for')) $input = $this->crawler->filter('#' . $label->attr('for'));
        }

        if (!isset($input)) $input = $this->match($field);
        if (!count($input)) throw new ElementNotFound($field, 'Form field by Label or CSS');
        return $input->first();

    }

    public function selectOption($select, $option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($select));
        $fieldName = $field->attr('name');
        if ($field->attr('multiple')) $fieldName = str_replace('[]', '', $fieldName);

        if (is_array($option)) {
            $options = array();
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
        if ($options->count()) return $options->first()->attr('value');
        return $option;
    }

    public function checkOption($option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($option));
        $form[$field->attr('name')]->tick();
    }

    public function uncheckOption($option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($option));
        $form[$field->attr('name')]->untick();
    }

    public function attachFile($field, $filename)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($field));
        $path = \Codeception\Configuration::dataDir() . $filename;
        if (!is_readable($path)) $this->fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        $form[$field->attr('name')]->upload($path);
    }

    public function sendAjaxGetRequest($uri, $params = array())
    {
        $this->sendAjaxRequest('GET', $uri, $params);
    }

    public function sendAjaxPostRequest($uri, $params = array())
    {
        $this->sendAjaxRequest('POST', $uri, $params);
    }

    public function sendAjaxRequest($method, $uri, $params = array())
    {
        $this->client->request($method, $uri, $params, array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $this->debugSection('Response', $this->getResponseStatusCode());
        $this->debugSection('Page', $this->client->getHistory()->current()->getUri());
        $this->debugSection('Cookies', json_encode($this->client->getRequest()->getCookies()));
        $this->debugSection('Headers', json_encode($this->client->getResponse()->getHeaders()));
    }

    protected function getResponseStatusCode()
    {
        // depending on Symfony version
        $response = $this->client->getInternalResponse();
        if (method_exists($response, 'getStatus')) return $response->getStatus();
        if (method_exists($response, 'getStatusCode')) return $response->getStatusCode();
        return "N/A";
    }

    protected function escape($string)
    {
        return (string)$string;
    }

    protected function match($selector)
    {
        try {
            $selector = CssSelector::toXPath($selector);
        } catch (ParseException $e) {
        }
        if (!Locator::isXPath($selector)) return null;

        return @$this->crawler->filterXPath($selector);
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $nodes = $this->match($cssOrXPathOrRegex);
        if ($nodes) {
            return $nodes->first()->text();
        }
        if (@preg_match($cssOrXPathOrRegex, $this->client->getInternalResponse()->getContent(), $matches)) {
            return $matches[1];
        }
        throw new ElementNotFound($cssOrXPathOrRegex, 'Element that matches CSS or XPath or Regex');
    }

    public function grabValueFrom($field)
    {
        $nodes = $this->match($field);
        if (!$nodes->count()) throw new ElementNotFound($field, 'Field');

        if ($nodes->filter('textarea')->count()) {
            return $nodes->filter('textarea')->text();
        }
        if ($nodes->filter('input')->count()) {
            return $nodes->filter('input')->attr('value');
        }

        if ($nodes->filter('select')->count()) {
            $select = $nodes->filter('select');
            $is_multiple = $select->attr('multiple');
            $results = array();
            foreach ($select->childNodes as $option) {
                if ($option->getAttribute('selected') == 'selected') {
                    $val = $option->attr('value');
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

    public function setCookie($name, $val)
    {
        $cookies = $this->client->getCookieJar();
        $cookies->set(new Cookie($name, $val));
        $this->debugSection('Cookies', json_encode($this->client->getCookieJar()->all()));
    }

    public function grabCookie($name)
    {
        $this->debugSection('Cookies', json_encode($this->client->getCookieJar()->all()));
        $cookies = $this->client->getCookieJar()->get($name);
        if (!$cookies) {
            $this->fail("Cookie by name '$name' not found");
        }
        return $cookies->getValue();
    }

    public function seeCookie($name)
    {
        $this->debugSection('Cookies', json_encode($this->client->getCookieJar()->all()));
        $this->assertNotNull($this->client->getCookieJar()->get($name));
    }

    public function dontSeeCookie($name)
    {
        $this->debugSection('Cookies', json_encode($this->client->getCookieJar()->all()));
        $this->assertNull($this->client->getCookieJar()->get($name));
    }

    public function resetCookie($name)
    {
        $this->client->getCookieJar()->expire($name);
        $this->debugSection('Cookies', json_encode($this->client->getCookieJar()->all()));
    }


    public function seeElement($selector)
    {
        $nodes = $this->match($selector);
        $this->assertDomContains($nodes, $selector);
    }

    public function dontSeeElement($selector)
    {
        $nodes = $this->match($selector);
        $this->assertDomNotContains($nodes, $selector);
    }

    public function seeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
        $this->assertDomContains($selected, 'selected option');
        $this->assertEquals($optionText, $selected->text());
    }

    public function dontSeeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
        if (!$selected->count()) {
            $this->assertEquals(0, $selected->count());
            return;
        }
        $this->assertNotEquals($optionText, $selected->text());
    }

    protected function matchSelectedOption($select)
    {
        $nodes = $this->getFieldByLabelOrCss($select);
        return $nodes->first()->filter('option[selected]');
    }

    public function seePageNotFound()
    {
        $this->seeResponseCodeIs(404);
    }

    public function seeResponseCodeIs($code)
    {
        $this->assertEquals($code, $this->getResponseStatusCode());
    }

    public function seeInTitle($title)
    {
        $nodes = $this->crawler->filter('title');
        if (!$nodes->count()) throw new ElementNotFound("<title>","Tag");
        $this->assertContains($title, $nodes->first()->text(), "page title contains $title");
    }

    public function dontSeeInTitle($title)
    {
        $nodes = $this->crawler->filter('title');
        if (!$nodes->count()) return $this->assertTrue(true);
        $this->assertNotContains($title, $nodes->first()->text(), "page title contains $title");
    }

    protected function assertDomContains($nodes, $message, $text = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\Crawler($text, $this->_getCurrentUri());
        $this->assertThat($nodes, $constraint, $message);
    }

    protected function assertDomNotContains($nodes, $message, $text = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\CrawlerNot($text, $this->_getCurrentUri());
        $this->assertThat($nodes, $constraint, $message);
    }

    protected function assertPageContains($needle, $message = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\Page($needle, $this->_getCurrentUri());
        $this->assertThat($this->client->getInternalResponse()->getContent(), $constraint,$message);
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $constraint = new \Codeception\PHPUnit\Constraint\Page($needle, $this->_getCurrentUri());
        $this->assertThatItsNot($this->client->getInternalResponse()->getContent(), $constraint,$message);
    }


}
