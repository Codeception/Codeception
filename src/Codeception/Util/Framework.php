<?php
namespace Codeception\Util;

use \Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract module for PHP frameworks connected via Symfony BrowserKit components
 * Each framework is connected with it's own connector defined in \Codeception\Util\Connector
 * Each module for framework should extend this class.
 *
 */

abstract class Framework extends \Codeception\Module implements FrameworkInterface
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

    protected $forms = array();

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        if (!$this->client || !$this->client->getResponse()) return;
        file_put_contents(\Codeception\Configuration::logDir() . basename($test->getFileName()) . '.page.debug.html', $this->client->getResponse()->getContent());
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->client = null;
        $this->crawler = null;
        $this->forms = array();

    }

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

        $anchor = $this->crawler->filterXPath('.//a[.='.$literal.']');
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

        if ($nodes->count()) {
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
        \PHPUnit_Framework_Assert::fail("Link or button for '$link' was not found");
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
        if (!$selector)
            return \PHPUnit_Framework_Assert::assertGreaterThan(0, $this->crawler->filter('html:contains("' . $this->escape($text) . '")')->count(), "$text in\n" . self::formatResponse($this->client->getResponse()->getContent()));
        $nodes = $this->match($selector);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $nodes->filter(':contains("' . $this->escape($text) . '")')->count(), " within selector '$selector' in\n" . self::formatResponse($this->client->getResponse()->getContent()));
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector)
            return $this->assertEquals(0, $this->crawler->filter('html:contains("' . $this->escape($text) . '")')->count(), "$text on page \n" . self::formatResponse($this->client->getResponse()->getContent()));
        $nodes = $this->match($selector);
        $this->assertEquals(0, $nodes->filter(':contains("' . $this->escape($text) . '")')->count(), "'$text'' within CSS selector '$selector' in\n" . self::formatResponse($this->client->getResponse()->getContent()));
    }

    public function seeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($this->escape($text));
        if (!$url) \PHPUnit_Framework_Assert::assertGreaterThan(0, $links->count(), "'$text' on page");
        $links->filterXPath(sprintf('descendant-or-self::a[contains(@href, "%s")]', Crawler::xpathLiteral(' ' . $this->escape($url) . ' ')));
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $links->count());
    }

    public function dontSeeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($this->escape($text));
        if (!$url) \PHPUnit_Framework_Assert::assertEquals(0, $links->count(), "'$text' on page");
        $links->filterXPath(sprintf('descendant-or-self::a[contains(@href, "%s")]', Crawler::xpathLiteral(' ' . $this->escape($url) . ' ')));
        \PHPUnit_Framework_Assert::assertEquals(0, $links->count());
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
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $checkboxes->filter('input[checked=checked]')->count());
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
        if (empty($field)) $this->fail("input field not found");
        $currentValue = $field->filter('textarea')->extract(array('_text'));
        if (!$currentValue) $currentValue = $field->extract(array('value'));
        return array('Contains', $this->escape($value), $currentValue);
    }

    public function submitForm($selector, $params)
    {
        $form = $this->match($selector)->first();

        if (!count($form)) return \PHPUnit_Framework_Assert::fail(', form does not exists');

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

        $url .= '&' . http_build_query($params);
        parse_str($url, $params);
   
        $method = $form->attr('method') ? $form->attr('method') : 'GET';

        $this->debugSection('Uri', $this->getFormUrl($form));
        $this->debugSection('Method', $method);
        $this->debugSection('Parameters', json_encode($params));

        $this->crawler = $this->client->request($method, $this->getFormUrl($form), $params);
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
        if (!$form) \PHPUnit_Framework_Assert::fail('The selected node does not have a form ancestor.');
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
        if (!count($input)) \PHPUnit_Framework_Assert::fail("Form field for '$field' not found on page");
        return $input->first();

    }

    public function selectOption($select, $option)
    {
        $form = $this->getFormFor($field = $this->getFieldByLabelOrCss($select));

        $options = $field->filter(sprintf('option:contains(%s)', $option));
        if ($options->count()) {
            $form[$field->attr('name')]->select($options->first()->attr('value'));
            return;
        }

        $form[$field->attr('name')]->select($option);
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
        if (!is_readable($path)) \PHPUnit_Framework_Assert::fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        $form[$field->attr('name')]->upload($path);
    }

    public function sendAjaxGetRequest($uri, $params = array())
    {
        $this->client->request('GET', $uri, $params, array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->debugResponse();
    }

    public function sendAjaxPostRequest($uri, $params = array())
    {
        $this->client->request('POST', $uri, $params, array(), array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'));
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $this->debugSection('Response', $this->getResponseStatusCode());
        $this->debugSection('Page', $this->client->getHistory()->current()->getUri());
    }

    protected function getResponseStatusCode()
    {
        // depending on Symfony version
        $response = $this->client->getResponse();
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
            $selector = \Symfony\Component\CssSelector\CssSelector::toXPath($selector);
        } catch (\Symfony\Component\CssSelector\Exception\ParseException $e) {
        }
        return @$this->crawler->filterXPath($selector);
    }

    public static function formatResponse($response)
    {
        if (strlen($response) <= 500) {
            $response = trim($response);
            $response = preg_replace('/\s[\s]+/',' ',$response); // strip spaces
            $response = str_replace("\n",'', $response);
            return $response;
        }
        if (strpos($response, '<html') !== false) {
            $formatted = 'page [';
            $crawler = new \Symfony\Component\DomCrawler\Crawler($response);
            $title = $crawler->filter('title');
            if (count($title)) $formatted .= "Title: " . trim($title->first()->text());
            $h1 = $crawler->filter('h1');
            if (count($h1)) $formatted .= "\nH1: " . trim($h1->first()->text());
            return $formatted. "]";
        }
        return "page.";
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $nodes = $this->match($cssOrXPathOrRegex);
        if ($nodes) {
            return $nodes->first()->text();
        }
        if (@preg_match($cssOrXPathOrRegex, $this->client->getResponse()->getContent(), $matches)) {
            return $matches[1];
        }
        $this->fail("Element that matches '$cssOrXPathOrRegex' not found");

    }

    public function grabValueFrom($field)
    {
        $nodes = $this->match($field);
        if ($nodes) {

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
        }
    }

    public function seeElement($selector)
    {
        $nodes = $this->match($selector);
        $this->assertGreaterThen(0, $nodes->count());
    }

    public function dontSeeElement($selector)
    {
        $nodes = $this->match($selector);
        $this->assertEquals(0, $nodes->count());
    }

    public function seeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
        $this->assertGreaterThen(0, $selected->count(), " no option is selected");
        $this->assertEquals($optionText, $selected->text());
    }

    public function dontSeeOptionIsSelected($select, $optionText)
    {
        $selected = $this->matchSelectedOption($select);
        $this->assertGreaterThen(0, $selected->count(), " no option is selected");
        if (!$selected->count()) {
            \PHPUnit_Framework_Assert::assertEquals(0, $selected->count);
            return;
        }
        $this->assertNotEquals($optionText, $selected->text());
    }

    protected function matchSelectedOption($select)
    {
        $nodes = $this->match($select);
        $this->assertGreaterThen(0, $nodes->count(), " select '$select' not found on page'");
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

}
