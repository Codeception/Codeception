<?php
namespace Codeception\Lib\Connector\Phalcon;

use Phalcon\Session\AdapterInterface;

class MemorySession implements AdapterInterface
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var array
     */
    protected $memory = [];

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options = null)
    {
        $this->sessionId = $this->generateId();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if ($this->status() !== PHP_SESSION_ACTIVE) {
            $this->memory = [];
            $this->started = true;

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if (isset($options['uniqueId'])) {
            $this->sessionId = $options['uniqueId'];
        }

        $this->options = $options;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @param mixed $defaultValue
     * @param bool $remove
     * @return mixed
     */
    public function get($index, $defaultValue = null, $remove = false)
    {
        $key = $this->prepareIndex($index);

        if (!isset($this->memory[$key])) {
            return $defaultValue;
        }

        $return = $this->memory[$key];

        if ($remove) {
            unset($this->memory[$key]);
        }

        return $return;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @param mixed $value
     */
    public function set($index, $value)
    {
        $this->memory[$this->prepareIndex($index)] = $value;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @return bool
     */
    public function has($index)
    {
        return isset($this->memory[$this->prepareIndex($index)]);
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     */
    public function remove($index)
    {
        unset($this->memory[$this->prepareIndex($index)]);
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Returns the status of the current session
     *
     * ``` php
     * <?php
     * if ($session->status() !== PHP_SESSION_ACTIVE) {
     *     $session->start();
     * }
     * ?>
     * ```
     *
     * @return int
     */
    public function status()
    {
        if ($this->isStarted()) {
            return PHP_SESSION_ACTIVE;
        }

        return PHP_SESSION_NONE;
    }

    /**
     * @inheritdoc
     *
     * @param bool $removeData
     * @return bool
     */
    public function destroy($removeData = false)
    {
        if ($removeData) {
            if (!empty($this->sessionId)) {
                foreach ($this->memory as $key => $value) {
                    if (0 === strpos($key, $this->sessionId . '#')) {
                        unset($this->memory[$key]);
                    }
                }
            } else {
                $this->memory = [];
            }
        }

        $this->started = false;

        return true;
    }

    /**
     * @inheritdoc
     *
     * @param bool $deleteOldSession
     * @return \Phalcon\Session\AdapterInterface
     */
    public function regenerateId($deleteOldSession = true)
    {
        $this->sessionId = $this->generateId();

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Dump all session
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->memory;
    }

    /**
     * Alias: Gets a session variable from an application context
     *
     * @param string $index
     * @return mixed
     */
    public function __get($index)
    {
        return $this->get($index);
    }

    /**
     * Alias: Sets a session variable in an application context
     *
     * @param string $index
     * @param mixed $value
     */
    public function __set($index, $value)
    {
        $this->set($index, $value);
    }

    /**
     * Alias: Check whether a session variable is set in an application context
     *
     * @param  string $index
     * @return bool
     */
    public function __isset($index)
    {
        return $this->has($index);
    }

    /**
     * Alias: Removes a session variable from an application context
     *
     * @param string $index
     */
    public function __unset($index)
    {
        $this->remove($index);
    }

    private function prepareIndex($index)
    {
        if ($this->sessionId) {
            $key = $this->sessionId . '#' . $index;
        } else {
            $key = $index;
        }

        return $key;
    }

    /**
     * @return string
     */
    private function generateId()
    {
        return md5(time());
    }
}
