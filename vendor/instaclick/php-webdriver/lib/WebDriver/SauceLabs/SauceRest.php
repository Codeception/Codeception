<?php
/**
 * Copyright 2012 Anthon Pang. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver\SauceLabs;

use WebDriver\ServiceFactory;

/**
 * WebDriver\SauceLabs\SauceRest class
 *
 * @package WebDriver
 */
class SauceRest
{
    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $accessKey;

    /**
     * Constructor
     *
     * @param string $userName  Your Sauce user name
     * @param string $accessKey Your Sauce API key
     */
    public function __construct($userName, $accessKey)
    {
        $this->userName = $userName;
        $this->accessKey = $accessKey;
    }

    /**
     * Execute Sauce Labs REST API command
     *
     * @see http://saucelabs.com/docs/saucerest
     *
     * @param string $requestMethod HTTP request method
     * @param string $url           URL
     * @param mixed  $parameters    Parameters
     * 
     * @return mixed
     */
    protected function execute($requestMethod, $url, $parameters = null)
    {
        $extraOptions = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->userName . ':' . $this->accessKey,
        );

        $url = 'https://saucelabs.com/rest/v1/' . $url;

        list($rawResults, $info) = ServiceFactory::getInstance()->getService('service.curl')->execute($requestMethod, $url, $parameters, $extraOptions);

        return json_decode($rawResults, true);
    }

    /**
     * Get account details: /rest/v1/users/:userId (GET)
     *
     * @param string $userId
     *
     * @return array
     */
    public function getAccountDetails($userId)
    {
        return $this->execute('GET', 'users/' . $userId);
    }

    /**
     * Check account limits: /rest/v1/limits (GET)
     *
     * @return array
     */
    public function getAccountLimits()
    {
        return $this->execute('GET', 'limits');
    }

    /**
     * Create new sub-account: /rest/v1/users/:userId (POST)
     *
     * For "parterns", $accountInfo also contains 'plan' => (one of 'free', 'small', 'team', 'com', or 'complus')
     *
     * @param array $accountInfo array('username' => ..., 'password' => ..., 'name' => ..., 'email' => ...)
     *
     * @return array array('access_key' => ..., 'minutes' => ..., 'id' => ...)
     */
    public function createSubAccount($accountInfo)
    {
        return $this->execute('POST', 'users/' . $this->userName, $accountInfo);
    }

    /**
     * Update sub-account service plan: /rest/v1/users/:userId/subscription (POST)
     *
     * @param string $userId User ID
     * @param string $plan   Plan
     */
    public function updateSubAccount($userId, $plan)
    {
        return $this->execute('POST', 'users/' . $userId . '/subscription', array('plan' => $plan));
    }

    /**
     * Unsubscribe a sub-account: /rest/v1/users/:userId/subscription (DELETE)
     *
     * @param string $userId User ID
     */
    public function unsubscribeSubAccount($userId, $plan)
    {
        return $this->execute('DELETE', 'users/' . $userId . '/subscription');
    }

    /**
     * Get current account activity: /rest/v1/:userId/activity (GET)
     *
     * @return array
     */
    public function getActivity()
    {
        return $this->execute('GET', $this->userId . '/activity');
    }

    /**
     * Get historical account usage: /rest/v1/:userId/usage (GET)
     *
     * @param string $start Optional start date YYYY-MM-DD
     * @param string $end   Optional end date YYYY-MM-DD
     *
     * @return array
     */
    public function getUsage($start = null, $end = null)
    {
        $query = http_build_query(array(
            'start' => $start,
            'end' => $end,
        ));

        return $this->execute('GET', $this->userId . '/usage' . (strlen($query) ? '?' . $query : ''));
    }

    /**
     * Get jobs: /rest/v1/:userId/jobs (GET)
     *
     * @param boolean $full
     *
     * @return array
     */
    public function getJobs($full = null)
    {
        $query = http_build_query(array(
            'full' => (isset($full) && $full) ? 'true' : null,
        ));

        return $this->execute('GET', $this->userId . '/jobs' . (strlen($query) ? '?' . $query : ''));
    }

    /**
     * Get full information for job: /rest/v1/:userId/jobs/:jobId (GET)
     *
     * @param string $jobId
     *
     * @return array
     */
    public function getJob($jobId)
    {
        return $this->execute('GET', $this->userId . '/jobs/' . $jobId);
    }

    /**
     * Update existing job: /rest/v1/:userId/jobs/:jobId (PUT)
     *
     * @param string $jobId   Job ID
     * @param array  $jobInfo Job information
     *
     * @return array
     */
    public function updateJob($jobId, $jobInfo)
    {
        return $this->execute('PUT', $this->userId . '/jobs/' . $jobId, $jobInfo);
    }

    /**
     * Stop job: /rest/v1/:userId/jobs/:jobId/stop (PUT)
     *
     * @param string $jobId
     *
     * @return array
     */
    public function stopJob($jobId)
    {
        return $this->execute('', $this->userId . '/jobs/' . $jobId . '/stop');
    }

    /**
     * Get running tunnels for a given user: /rest/v1/:userId/tunnels (GET)
     *
     * @return array
     */
    public function getTunnels()
    {
        return $this->execute('GET', $this->userId . '/tunnels');
    }

    /**
     * Get full information for a tunnel: /rest/v1/:userId/tunnels/:tunnelId (GET)
     *
     * @param string $tunnelId
     *
     * @return array
     */
    public function getTunnel($tunnelId)
    {
        return $this->execute('GET', $this->userId . '/tunnels/' . $tunnelId);
    }

    /**
     * Shut down a tunnel: /rest/v1/:userId/tunnels/:tunnelId (DELETE)
     *
     * @param string $tunnelId
     *
     * @return array
     */
    public function shutdownTunnel($tunnelId)
    {
        return $this->execute('DELETE', $this->userId . '/tunnels/' . $tunnelId);
    }

    /**
     * Get current status of Sauce Labs' services: /rest/v1/info/status (GET)
     *
     * @return array array('wait_time' => ..., 'service_operational' => ..., 'status_message' => ...)
     */
    public function getStatus()
    {
        return $this->execute('GET', 'info/status');
    }

    /**
     * Get currently supported browsers: /rest/v1/info/browsers (GET)
     *
     * @return array
     */
    public function getBrowsers()
    {
        return $this->execute('GET', 'info/browsers');
    }

    /**
     * Get number of tests executed so far on Sauce Labs: /rest/v1/info/counter (GET)
     *
     * @return array
     */
    public function getCounter()
    {
        return $this->execute('GET', 'info/counter');
    }
}
