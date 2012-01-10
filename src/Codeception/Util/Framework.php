<?php
namespace Codeception\Util;

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

    public function amOnPage($page)
    {
        $this->crawler = $this->client->request('GET', $page);
        $this->debugSection('Response', $this->client->getResponse()->getStatus());
    }

    public function click($link)
    {
        $link = $this->crawler->selectLink($link);
        if (!empty($link)) {
            $this->crawler = $this->client->click($link);
            $this->debugResponse();
            return;
        }
        $button = $this->crawler->selectButton($link);
        if (!empty($button)) {
            $this->submitFormWithButton($button);
            $this->debugResponse();
            return;
        }
        \PHPUnit_Framework_Assert::fail("Link or button for '$link' was not found");
    }

    protected function submitFormWithButton($button) {
        $domForm = $button->form();
        $form = $this->getFormFor($button);

        $this->debugSection('Uri', $domForm->getUri());
        $this->debugSection('Method', $domForm->getMethod());
        $this->debugSection('Parameters', $form->getValues());

        $this->crawler = $this->client->request($domForm->getMethod(), $domForm->getUri(), $form->getPhpValues(), $form->getPhpFiles());
    }

    public function see($text, $selector = null)
    {
        if (!$selector)
            return \PHPUnit_Framework_Assert::assertGreaterThan(0, $this->crawler->filter('html:contains("' . $text . '")')->count(), "$text on page \n".$this->formatHtmlResponse());
        \PHPUnit_Framework_Assert::assertAttributeGreaterThan(0, $this->crawler->filter($selector . ':contains("' . $text . '")')->count(), "'$text'' within selector '$selector' on page \n".$this->formatHtmlResponse());
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector)
            return \PHPUnit_Framework_Assert::assertEquals(0, $this->crawler->filter('html:contains("' . $text . '")')->count(), "$text on page \n".$this->formatHtmlResponse());
        \PHPUnit_Framework_Assert::assertAttributeEquals(0, $this->crawler->filter($selector . ':contains("' . $text . '")')->count(), "'$text'' within selector '$selector' on page \n".$this->formatHtmlResponse());
    }

    protected function formatHtmlResponse()
    {
        $response = $this->client->getResponse()->getContent();
        if (strpos($response, '<!DOCTYPE') !== false) {
            $formatted = $this->crawler->filter('title')->first()->text();
            $h1 = $this->crawler->filter('h1')->first()->text();
            $formatted .= '. Contents are to long to display here, you can see the response in Codeception log directory.';
            return $formatted;
        }
        return $response;
    }

    public function seeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($text);
        if (!$url) \PHPUnit_Framework_Assert::assertGreaterThan(0, $links->count(), "'$text' on page");
        $links->filter('a[href="' . $url . '"]');
        \PHPUnit_Framework_Assert::assertContains($url, $this->crawler->extract(array('href')), " with text $text and url $url");
    }

    public function dontSeeLink($text, $url = null)
    {
        $links = $this->crawler->selectLink($text);
        if (!$url) \PHPUnit_Framework_Assert::assertEquals(0, $links->count(), "'$text' on page");
        $links->filter('a[href="' . $url . '"]');
        \PHPUnit_Framework_Assert::assertNotContains($url, $this->crawler->extract(array('href')), " with text $text and url $url");
    }

    public function seeInCurrentUrl($uri)
    {
        \PHPUnit_Framework_Assert::assertContains($uri, $this->client->getHistory()->current());
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
        return array('Contains', $value, array_merge($values1, $values2));
    }

    public function submitForm($selector, $params)
    {
        $form = $this->crawler->filter($selector)->first();
        if (empty($form)) return \PHPUnit_Framework_Assert::fail(', form does not exists');

        $fields = $this->crawler->filter($selector . ' input');

        $url = '';
        foreach ($fields as $field) {
            if ($field->attr('type') == 'checkbox') continue;
            if ($field->attr('type') == 'radio') continue;
            $url .= sprintf('%s=%s', $field->attr('name'), $field->attr('value')) . '&';
        }

        $fields = $this->crawler->filter($selector . ' textarea');
        foreach ($fields as $field) {
            $url .= sprintf('%s=%s', $field->attr('name'), $field->text()) . '&';
        }

        $fields = $this->session->getPage()->findAll('css', $selector . ' select');
        foreach ($fields as $field) {
            foreach ($field->children() as $option) {
                if ($option->attr('selected') == 'selected')
                    $url .= sprintf('%s=%s', $field->attr('name'), $option->getValue()) . '&';
            }
        }

        $url .= '&' . http_build_query($params);
        parse_str($url, $params);
        $method = $form->attr('method') ? $form->attr('method') : 'GET';

        $this->debugSection('Uri', $form->attr('action'));
        $this->debugSection('Method', $method);
        $this->debugSection('Parameters', $params);

        $this->crawler = $this->client->request($method, $form->attr('action'), $params);
        $this->debugResponse();
    }

    protected function getFormFor($node)
    {
        do {
            // use the ancestor form element
            if (null === $node = $node->parentNode) {
                throw new \LogicException('The selected node does not have a form ancestor.');
            }
        } while ('form' != $node->nodeName);
        $xpath = $node->toXpath();

        if (isset($this->forms[$xpath])) return $this->forms[$xpath];

        $crawler = new \Symfony\Component\DomCrawler\Crawler('<form action="#" method="GET"><input type="SUBMIT" value="submit"/></form>');
        $form = $crawler->filter('input')->form();
        $this->forms[$xpath] = $form;
        return $form;
    }

    public function fillField($field, $value)
    {
        $form = $this->getFormFor($field = $this->crawler->filter($field)->first());
        $form[$field->attr('name')] = $value;
    }

    public function selectOption($select, $option)
    {
        $form = $this->getFormFor($field = $this->crawler->filter($select)->first());
        $form[$field->attr('name')]->select($option);
    }

    public function checkOption($option) {
        $form = $this->getFormFor($field = $this->crawler->filter($option)->first());
        $form[$field->attr('name')]->tick();
    }

    public function uncheckOption($option) {
        $form = $this->getFormFor($field = $this->crawler->filter($option)->first());
        $form[$field->attr('name')]->untick();
    }

    public function attachFile($field, $filename) {
        $form = $this->getFormFor($field = $this->crawler->filter($field)->first());
        $path = \Codeception\Configuration::dataDir().$filename;
        if (!file_exists($path)) \PHPUnit_Framework_Assert::fail("file $filename not found in Codeception data path. Only files stored in data path accepted");
        $form[$field->attr('name')]->upload($filename);
    }
    
    public function sendAjaxGetRequest($uri, $params = array()) {
        $this->client->request('GET', $uri, $params, array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest'));
        $this->debugResponse();
    }

    public function sendAjaxPostRequest($uri, $params = array()) {
        $this->client->request('POST', $uri, $params, array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest'));
        $this->debugResponse();
    }

    protected function debugResponse()
    {
        $this->debugSection('Response', $this->client->getResponse()->getStatus());
        $this->debugSection('Page', $this->client->getHistory()->current());
    }

}
