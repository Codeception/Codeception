<?php
namespace Codeception\Lib\Connector\Phalcon;

use Phalcon\Session\AdapterInterface;

class MemorySession34x extends MemorySession implements AdapterInterface
{
    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     *
     * @param string $index
     * @return bool
     */
    public function has(string $index): bool
    {
        return isset($this->memory[$this->prepareIndex($index)]);
    }


    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->sessionId;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * @inheritdoc
     *
     * @param bool $removeData
     * @return bool
     */
    public function destroy($removeData = null): bool
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
    public function regenerateId(bool $deleteOldSession = null): \Phalcon\Session\AdapterInterface
    {
        $this->sessionId = $this->generateId();

        return $this;
    }
    
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
