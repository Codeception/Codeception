<?php
/**
 * @author tiger
 */

namespace Codeception\Module;

use Codeception\Module as BaseModule;
use Codeception\Util\Driver\Facebook as FacebookDriver;

/**
 *
 * ## Status
 * * Maintainer: **tiger-seo**
 * * Stability: **alpha**
 * * Contact: tiger.seo@gmail.com
 *
 * ## Config
 *
 * * appId - Facebook application ID
 * * secret - Facebook application secret
 *
 * ### Example
 *
 *     modules:
 *         enabled: [Facebook]
 *         config:
 *             Facebook:
 *                 appId: 412345678901234
 *                 secret: ccb79c1b0fdff54e4f7c928bf233aea5
 *
 * @since 1.6
 * @author tiger.seo@gmail.com
 */
class Facebook extends BaseModule
{
    protected $requiredFields = array('appId', 'secret');

    /**
     * @var FacebookDriver
     */
    protected $facebook;

    public function _initialize()
    {
        $this->facebook = new FacebookDriver(array(
                                               'appId'  => $this->config['appId'],
                                               'secret' => $this->config['secret'],
                                          ));
    }

    public function grabAccessToken()
    {
        $this->facebook->getAccessToken();
    }
}
