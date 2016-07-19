<?php
namespace Codeception\Lib\Connector\Yii2;

use yii\test\FixtureTrait;

class FixturesStore
{
    use FixtureTrait;

    protected $data;

    /**
     * Expects fixtures config
     *
     * FixturesStore constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function fixtures()
    {
        return $this->data;
    }
}
