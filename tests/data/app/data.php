<?php
class data {

    public static $filename = '/db';

    public static function get($key) {
        $data = self::load();
        return $data[$key];
    }

    public static function set($key, $value)
    {
        $data = self::load();
        $data[$key] = $value;
        self::save($data);
    }

    public static function remove($key)
    {
        $data = self::load();
        unset($data[$key]);
        self::save($data);
    }

    public static function clean()
    {
        self::save(array());
    }

    protected static function load()
    {
        $data = file_get_contents(__DIR__.self::$filename);
        $data = $data ? unserialize($data) : $data = array();
        if (!is_array($data)) $data = array();
        return $data;
    }

    protected static function save($data)
    {
        file_put_contents(__DIR__.self::$filename, serialize($data));
    }
}