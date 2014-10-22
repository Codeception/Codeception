<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfig;
use Codeception\Lib\Driver\HipChat;

/**
 *
 * Works with IM services.
 *
 * Testing with a selection of remote instant messaging services, including HipChat.
 *
 * Supported and tested queue types are:
 *
 * * [HipChat](http://hipchat.com/)
 *
 * The following dependencies are needed for the listed IM services:
 *
 * * HipChat: "gorkalaucirica/hipchat-v2-api-client": "~1.0"
 *
 * ## Status
 *
 * * Maintainer: **nathanmac**
 * * Stability:
 *     - HipChat:    **stable**
 * * Contact: nathan.macnamara@outlook.com
 *
 * ## Config
 *
 * The configuration settings depending on which queueing service is being used, all the options are listed
 * here. Refer to the configuration examples below to identify the configuration options required for your chosen
 * service.
 *
 * * service - the messaging service.
 * * token - API token and/or Access Token.
 * * room - The room/channel id for the messaging service.
 *
 * N.B. The API key for HipChat must be an users API token in order to provide sufficient access the last messages in
 *      a given room, however if you are just looking to send a messages to the service a room API token will be
 *      sufficient to send the message. Consult the HipChat API documentation for clarification on this topic.
 *
 * ### Example
 * #### Example (hipchat)
 *
 *     modules:
 *        enabled: [IM]
 *        config:
 *           IM:
 *              service: 'hipchat'
 *              token: API_TOKEN
 *              room: ROOM_ID
 *
 */
class IM extends \Codeception\Module
{

    /**
     * @var \Codeception\Lib\Interfaces\IM
     */
    public $IMDriver;

    /**
     * Setup connection and open/setup the connection with config settings
     *
     * @param \Codeception\TestCase $test
     */
    public function _before(\Codeception\TestCase $test)
    {
        $this->IMDriver->setup($this->config);
    }

    /**
     * Provide and override for the config settings and allow custom settings depending on the service being used.
     */
    protected function validateConfig()
    {
        $this->IMDriver = $this->createIMDriver();
        $this->requiredFields = $this->IMDriver->getRequiredConfig();
        $this->config = array_merge($this->IMDriver->getDefaultConfig(), $this->config);
        parent::validateConfig();
    }

    /**
     * @return \Codeception\Lib\Interfaces\IM
     * @throws ModuleConfig
     */
    protected function createIMDriver()
    {
        switch (strtolower($this->config['service'])) {
            case 'hipchat':
                return new HipChat();
            default:
                throw new ModuleConfig(
                    __CLASS__, "Unknown IM service {$this->config}; Supported IM services include: HipChat"
                );
        }
    }

    // ----------- METHODS BELOW HERE ------------------------//

    /**
     * Method for sending a messages to an messaging service.
     *
     * ```php
     * <?php
     * $I->sendInstantMessage('Testing message to be send to IM service.', array('color' => 'red', 'notify' => true);
     * ?>
     * ```
     *
     * @param string $message Message to be sent.
     * @param array $options Depends on service, for HipChat these include the color and the notify option.
     */
    public function sendInstantMessage($message, $options = array())
    {
        $this->IMDriver->sendMessage($message, $options);
    }

    /**
     * Grabber method to return the last messages on the service.
     *
     * ```php
     * <?php
     * $messages = $I->grabLastInstantMessage();
     * ?>
     * ```
     *
     * @return array
     */
    public function grabLastInstantMessage(){
        $message = $this->IMDriver->grabLastMessage();
        $this->debug("Message -->");
        $this->debug($message);
        return $message;
    }

    /**
     * Grabber method to return the from field of the last message.
     *
     * ```php
     * <?php
     * $from = $this->grabLastInstantMessageFrom();
     * ?>
     * ```
     *
     * @return string
     */
    public function grabLastInstantMessageFrom()
    {
        $from = $this->IMDriver->grabLastMessageFrom();
        $this->debug('From: [' . $from . ']');
        return $from;
    }

    /**
     * Checks whether the last messages from address matches.
     *
     * ```php
     * <?php
     * $I->seeInLastInstantMessageFrom('Codeception');
     * ?>
     * ```
     *
     * @param string $from
     */
    public function seeInLastInstantMessageFrom($from)
    {
        \PHPUnit_Framework_Assert::assertEquals($from, $this->grabLastInstantMessageFrom());
    }

    /**
     * Checks whether the last messages from address does not match.
     *
     * ```php
     * <?php
     * $I->dontSeeInLastInstantMessageFrom('Codeception');
     * ?>
     * ```
     *
     * @param string $from
     */
    public function dontSeeInLastInstantMessageFrom($from)
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($from, $this->grabLastInstantMessageFrom());
    }

    /**
     * Grabber method to return the content/text of the last message.
     *
     * ```php
     * <?php
     * $from = $this->grabLastInstantMessageContent();
     * ?>
     * ```
     *
     * @return string
     */
    public function grabLastInstantMessageContent()
    {
        $content = $this->IMDriver->grabLastMessageContent();
        $this->debug('Content: [' . $content . ']');
        return $content;
    }

    /**
     * Checks whether the last messages content/text matches.
     *
     * ```php
     * <?php
     * $I->seeInLastInstantMessageContent('Hello this is the messages I am expecting to see.');
     * ?>
     * ```
     *
     * @param string $content
     */
    public function seeInLastInstantMessageContent($content)
    {
        \PHPUnit_Framework_Assert::assertEquals($content, $this->grabLastInstantMessageContent());
    }

    /**
     * Checks whether the last messages content/text does not match.
     *
     * ```php
     * <?php
     * $I->dontSeeInLastInstantMessageContent('Hello this is the messages I am not expecting to see.');
     * ?>
     * ```
     *
     * @param string $content
     */
    public function dontSeeInLastInstantMessageContent($content)
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($content, $this->grabLastInstantMessageContent());
    }

    /**
     * Grabber method to return the color of the last message.
     *
     * ```php
     * <?php
     * $from = $this->grabLastInstantMessageColor();
     * ?>
     * ```
     *
     * @return string
     */
    public function grabLastInstantMessageColor()
    {
        $color = $this->IMDriver->grabLastMessageColor();
        $this->debug('Color: [' . $color . ']');
        return $color;
    }

    /**
     * Checks whether the last messages color matches.
     *
     * ```php
     * <?php
     * $I->seeInLastInstantMessageColor('red');
     * ?>
     * ```
     *
     * @param string $color
     */
    public function seeInLastInstantMessageColor($color)
    {
        \PHPUnit_Framework_Assert::assertEquals($color, $this->grabLastInstantMessageColor());
    }

    /**
     * Checks whether the last messages color does not match.
     *
     * ```php
     * <?php
     * $I->dontSeeInLastInstantMessageColor('red');
     * ?>
     * ```
     *
     * @param string $color
     */
    public function dontSeeInLastInstantMessageColor($color)
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($color, $this->grabLastInstantMessageColor());
    }

    /**
     * Grabber method to return the date/time of the last message.
     *
     * ```php
     * <?php
     * $from = $this->grabLastInstantMessageDate();
     * ?>
     * ```
     *
     * @return string
     */
    public function grabLastInstantMessageDate()
    {
        $date = $this->IMDriver->grabLastMessageDate();
        $this->debug('Date/Time: [' . $date . ']');
        return $date;
    }

    /**
     * Checks whether the last messages date matches.
     *
     * ```php
     * <?php
     * $I->seeInLastInstantMessageDate('2014-10-13T16:30:48.657455+00:00');
     * ?>
     * ```
     *
     * @param string $date
     */
    public function seeInLastInstantMessageDate($date)
    {
        \PHPUnit_Framework_Assert::assertEquals($date, $this->grabLastInstantMessageDate());
    }

    /**
     * Checks whether the last messages date does not match.
     *
     * ```php
     * <?php
     * $I->dontSeeInLastInstantMessageDate('2015-11-13T16:30:48.657455+00:00');
     * ?>
     * ```
     *
     * @param string $date
     */
    public function dontSeeInLastInstantMessageDate($date)
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($date, $this->grabLastInstantMessageDate());
    }

}