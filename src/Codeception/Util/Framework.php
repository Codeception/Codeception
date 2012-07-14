<?php
namespace Codeception\Util;

use \Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract module for PHP framworks connected via Symfony BrowserKit components
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
        if (!$this->client->getResponse()) return;
        file_put_contents(\Codeception\Configuration::logDir() . basename($test->getFileName()) . '.page.debug.html', $this->client->getResponse()->getContent());
    }

    public function _after(\Codeception\TestCase $test)
    {
        $this->client = null;
        $this->crawler = null;
        $this->forms = array();

    }

    public function amOnPage($page)
    {
        $this->crawler = $this->client->request('GET', $page);
        $this->debugResponse();
    }

    public function click($link)
    {
        $link = $this->escape($link);
        $anchor = $this->crawler->selectLink($link);
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

        $nodes = $this->crawler->filter($link);
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
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $this->crawler->filter($selector . ':contains("' . $this->escape($text) . '")')->count(), " within CSS selector '$selector' in\n" . self::formatResponse($this->client->getResponse()->getContent()));
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector)
            return \PHPUnit_Framework_Assert::assertEquals(0, $this->crawler->filter('html:contains("' . $this->escape($text) . '")')->count(), "$text on page \n" . self::formatResponse($this->client->getResponse()->getContent()));
        \PHPUnit_Framework_Assert::assertEquals(0, $this->crawler->filter($selector . ':contains("' . $this->escape($text) . '")')->count(), "'$text'' within CSS selector '$selector' in\n" . self::formatResponse($this->client->getResponse()->getContent()));
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

    public function seeInCurrentUrl($uri)
    {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->client->getHistory()->current()->getUri());
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
        $fields = $this->crawler->filter($field);
        $values1 = $fields->filter('input')->extract(array('value'));
        $values2 = $fields->filter('textarea')->extract(array('_text'));
        if (empty($values1) && empty($values2)) \PHPUnit_Framework_Assert::fail('field not found');
        $values = array_merge($values1, $values2);
        return array('Contains', $this->escape($value), $values);
    }

    public function submitForm($selector, $params)
    {
        $form = $this->crawler->filter($selector)->first();

        if (!count($form)) return \PHPUnit_Framework_Assert::fail(', form does not exists');

        $url = '';
        $fields = $this->crawler->filter($selector . ' input');
        foreach ($fields as $field) {
            if ($field->getAttribute('type') == 'checkbox') continue;
            if ($field->getAttribute('type') == 'radio') continue;
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->getAttribute('value')) . '&';
        }

        $fields = $this->crawler->filter($selector . ' textarea');
        foreach ($fields as $field) {
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->nodeValue) . '&';
        }

        $fields = $this->crawler->filter($selector . ' select');
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
        $label = $this->crawler->filterXPath(sprintf('descendant-or-self::label[text()="%s"]', $field))->first();
        if (count($label) && $label->attr('for')) {
            $input = $this->crawler->filter('#' . $label->attr('for'));
        }

        if (!isset($input)) $input = $this->crawler->filter($field);
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
        $this->debugSection('Response', $this->client->getResponse()->getStatus());
        $this->debugSection('Page', $this->client->getHistory()->current()->getUri());
    }

    protected function escape($string)
    {
        return addslashes($string);
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


}