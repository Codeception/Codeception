<?php
namespace Codeception\Module;


class Symfony1 extends \Codeception\Module
{

    /**
     * @var \sfBrowser
     */
    protected $browser;
    protected $session_id;

    protected $requiredFields = array('app');

    public function _initialize()
    {
        if (!file_exists('config/ProjectConfiguration.class.php')) throw new \Codeception\Exception\Module('Symfony1', 'config/ProjectConfiguration.class.php not found. This file is required for running symfony1');
        require_once('config/ProjectConfiguration.class.php');
        $conf = \ProjectConfiguration::getApplicationConfiguration($this->config['app'], 'test', true);
        \sfContext::createInstance($conf,'default');
     	\sfContext::switchTo('default');

        // chdir(\sfConfig::get('sf_web_dir'));
        $this->browser = new \sfBrowser();
        $this->browser->get('/');
        \sfForm::disableCSRFProtection();
    }

    public function _getBrowser()
    {
        return $this->browser;
    }

    public function _cleanup()
    {
        chdir(\sfConfig::get('sf_web_dir'));
        $this->browser->restart();
        $this->session_id = $_SERVER['session_id'];
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->browser->get('/');
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        if (!isset($this->config['output'])) {
            $output = \sfConfig::get('sf_log_dir') . $test->getFileName() . '.page.debug.html';
        } else {
            $output = getcwd().DIRECTORY_SEPARATOR.$this->config['output'].DIRECTORY_SEPARATOR.$test->getFileName() . '.page.debug.html';
        }
        file_put_contents($output, $this->browser->getResponse()->getContent());
    }

    public function _after(\Codeception\TestCase $test)
    {

        $this->browser->restart();
        $this->browser->resetCurrentException();
        $this->browser->get('/');

    }

    protected function call($uri, $method = 'get', $params = array())
    {
        // multi testguy implementation
        $_SERVER['session_id'] = $this->session_id;

        if (false === ($empty = $this->browser->checkCurrentExceptionIsEmpty())) {
            \PHPUnit_Framework_Assert::fail(sprintf('last request threw an uncaught exception "%s: %s"', get_class($this->browser->getCurrentException()), $this->browser->getCurrentException()->getMessage()));
            return;
        }

        $this->debug('Request (' . $method . '): ' . $uri . ' ' . json_encode($params));

        $this->browser->call($uri, $method, $params);
        $this->debug('Response code: ' . $this->browser->getResponse()->getStatusCode());
        $this->debug('Response message: ' . $this->browser->getResponse()->getStatusText());

        if ($form = $this->getForm()) {
            foreach ($form->getErrorSchema() as $field => $desc) {
                $this->debug("Error in form $field field: '$desc'");
            }
        }

        $this->followRedirect();
    }

    protected function followRedirect()
    {
        if ($this->browser->getResponse()->getStatusCode() == 302) {
            $this->debug("redirected to " . $this->browser->getResponse()->getHttpHeader('Location'));
            $this->call($this->browser->getResponse()->getHttpHeader('Location'));
        }
    }

    public function amOnPage($page)
    {
        $this->browser->get($page);
        $this->debug('Response code: ' . $this->browser->getResponse()->getStatusCode());
        $this->followRedirect();
    }

    public function click($link)
    {
        $this->browser->click($link);
        $this->debug('moved to page ' . $this->browser->fixUri($this->browser->getRequest()->getUri()));
        $this->followRedirect();
    }

    public function dontSee($text, $selector = null)
    {
        $res = $this->proceedSee($text, $selector);
        $this->assertNot($res);
    }

    public function see($text, $selector = null)
    {
        $res = $this->proceedSee($text, $selector);
        $this->assert($res);
    }

    protected function proceedSee($text, $selector = null)
    {
        if ($selector) {
            $nodes = $this->browser->getResponseDomCssSelector()->matchAll($selector);
            $values = array();
            foreach ($nodes as $node) {
                $values[] = trim($node->nodeValue);
            }
            return array('contains', $text, $values, "'$selector' selector in " . $this->browser->getResponse()->getContent() . '. For more details look page snapshot in the log directory');
        }

        $response = $this->browser->getResponse()->getContent();
        if (strpos($response, '<!DOCTYPE') !== false) {
            $response = array();
            $title = $this->browser->getResponse()->getTitle();
            if ($title) $response['title'] = trim($title);
            $h1 = $this->browser->getResponseDomCssSelector()->matchSingle('h1')->getNode();
            if ($h1 && is_object($title)) $response['h1'] = trim($title->nodeValue);
            $response['uri'] = $this->browser->fixUri($this->browser->getRequest()->getUri());
            $response['responseCode'] = $this->browser->getResponse()->getStatusCode();
            $response = json_encode($response);
            $response = 'html page response ' . $response;
        }
        return array('contains', $text, strip_tags($this->browser->getResponse()->getContent()), "'$text' in " . $response . '. For more details look page snapshot in the log directory');
    }

    public function seeLink($text, $url = null)
    {
        if (!$url) return $this->see($text, 'a');
        $nodes = $this->browser->getResponseDomCssSelector()->matchAll('a')->getNodes();
        foreach ($nodes as $node) {
            if (0 === strrpos($node->getAttribute('href'), $url)) {
                return \PHPUnit_Framework_Assert::assertContains($text, $node->nodeValue, "with url '$url'");
            }
        }
    }

    public function dontSeeLink($text, $url = null)
    {
        if (!$url) return $this->dontSee($text, 'a');
        $nodes = $this->browser->getResponseDomCssSelector()->matchAll('a')->getNodes();
        foreach ($nodes as $node) {
            if (0 === strrpos($node->getAttribute('href'), $url) && ($node->nodeValue == trim($text))) {
                return \PHPUnit_Framework_Assert::assertFalse((0 === strrpos($node->getAttribute('href'), $url) && ($node->nodeValue == trim($text))), "link with url '$url'");
            }
        }
    }

    public function seeCheckboxIsChecked($selector)
    {
        $res = $this->proceedCheckboxIsChecked($selector);
        $this->assert($res);
    }

    public function dontSeeCheckboxIsChecked($selector)
    {
        $res = $this->proceedCheckboxIsChecked($selector);
        $this->assertNot($res);
    }

    protected function proceedCheckboxIsChecked($selector)
    {
        $node = $this->browser->getResponseDomCssSelector()->matchSingle($selector)->getNode();
        if (!$node) return PHPUnit_Framework_Assert::fail("there is no element $selector on page");
        if ($node->getAttribute('type') != 'checkbox') return PHPUnit_Framework_Assert::fail("there is no checkbox $selector on page");
        return array('equals', $node->getAttribute('checked'), 'checked', "that checkbox $selector is checked");
    }

    public function seeEmailReceived()
    {
        $this->debug('Emails sent: ' . $this->browser->getContext()->getMailer()->getLogger()->countMessages());
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $this->browser->getContext()->getMailer()->getLogger()->countMessages(), "");
    }


    public function submitForm($selector, $params)
    {

        $form = $this->browser->getResponseDomCssSelector()->matchSingle($selector)->getNode();
        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' input')->getNodes();
        $url = '';
        foreach ($fields as $field) {
            if ($field->getAttribute('type') == 'checkbox') continue;
            if ($field->getAttribute('type') == 'radio') continue;
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->getAttribute('value')) . '&';
        }

        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' textarea')->getNodes();
        foreach ($fields as $field) {
            $url .= sprintf('%s=%s', $field->getAttribute('name'), $field->nodeValue) . '&';
        }

        $fields = $this->browser->getResponseDomCssSelector()->matchAll($selector . ' select')->getNodes();
        foreach ($fields as $field) {
            foreach ($field->childNodes as $option) {
                if ($option->getAttribute('selected') == 'selected') {
                    $url .= sprintf('%s=%s', $field->getAttribute('name'), $option->getAttribute('value')) . '&';
                    break;
                }
            }
        }

        $url .= '&' . http_build_query($params);
        parse_str($url, $params);
        $url = $form->getAttribute('action');
        $method = $form->getAttribute('method');


        $this->call($url, $method, $params);
    }

    public function sendAjaxPostRequest($uri, $params)
    {
        $this->browser->setHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'post', $params);
        $this->debug($this->browser->getResponse()->getContent());
    }


    public function sendAjaxGetRequest($uri, $params)
    {
        $this->browser->setHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $this->call($uri, 'get', $params);
        $this->debug($this->browser->getResponse()->getContent());
    }


    public function clickSubmitButton($selector, $data = array())
    {
        $this->browser->click($selector, $data);
    }

    public function seeFormIsValid()
    {
        $form = $this->getForm();
        if (!$form) \PHPUnit_Framework_Assert::fail('as any symfony forms at all');
        \PHPUnit_Framework_Assert::isFalse($form->hasErrors(), ', seems like there were errors');
    }

    public function seeErrorsInForm()
    {
        $form = $this->getForm();
        if (!$form) \PHPUnit_Framework_Assert::fail('as any symfony forms at all');
        \PHPUnit_Framework_Assert::isTrue($form->hasErrors(), ', seems like there were no validation errors');
        foreach ($form->getErrorSchema() as $field => $desc) {
            $this->debug("Error in $field field: '$desc'");
        }
    }

    public function seeErrorInField($field)
    {
        $form = $this->getForm();
        $keys = array();
        foreach ($form->getErrorSchema() as $field => $desc) {
            $keys[] = $field;
        }
        $this->assert(array('contains', $field, $keys, "$field"));
    }

    protected function getForm()
    {
        $action = $this->browser->getContext()->getActionStack()->getLastEntry()->getActionInstance();

        foreach ($action->getVarHolder()->getAll() as $name => $value)
        {
            if ($value instanceof sfForm && $value->isBound()) {
                $form = $value;
                break;
            }
        }
        if (!isset($form)) return null;
        return $form;
    }

    public function signIn($username, $password)
    {
        $this->browser->post('/sfGuardAuth/signin', array('signin' => array('username' => $username, 'password' => $password)));
        $this->debug('session: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll()));
        $this->debug('user: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll('sfGuardSecurityUser')));
        $this->debug('credentials: ' . json_encode($this->browser->getUser()->getCredentials()));
        $this->browser->get('/');
        $this->followRedirect();
    }

    public function signOut()
    {
        $this->browser->get('/logout');
        $this->followRedirect();
    }

    public function am($name)
    {
        $this->amLoggedAs($name);
    }

    public function amLoggedAs($name)
    {
        if (!class_exists('Doctrine')) throw new \Exception('Doctrine is not installed. Consider using \'signIn\' action instead');
        $user = \Doctrine::getTable('sfGuardUser')->findOneBy('username', $name);
        if (!$user) throw new \Exception("User with name $name was not found in database");
        $user->clearRelated();
        $browser = $this->browser;
        $this->browser->getContext()->getStorage()->initialize($browser->getContext()->getStorage()->getOptions());
        $browser->getUser()->signIn($user);
        $this->debug('session: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll()));
        $this->debug('user: ' . json_encode($this->browser->getUser()->getAttributeHolder()->getAll('sfGuardSecurityUser')));
        $this->debug('credentials: ' . json_encode($this->browser->getUser()->getCredentials()));
        $this->browser->getUser()->shutdown();
        $this->browser->getContext()->getStorage()->shutdown();

        $browser->get('/');
        $this->followRedirect();
    }

}
