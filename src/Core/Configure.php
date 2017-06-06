<?php

namespace OpenFrame\Core;

/**
 *
 * @author Leandro de Amorim <androrim@gmail.com>
 */
class Configure
{

    private static $config;

    public static function init(array $config)
    {
        self::$config = $config;
    }

    public static function write($config, $value = null)
    {
        if (!is_array($config)) {
            $config = array($config => $value);
        }

        foreach ($config as $k => $v) {
            if (isset(self::$config[$k])) {
                self::$config[$k] = self::parseConfigValue($v);
            }
        }
    }

    private static function parseConfigValue($confValue)
    {
        $value = array();

        if (!is_array($confValue)) {
            return $confValue;
        }

        foreach ($confValue as $k => $v) {
            $value[$k] = self::parseConfigValue($v);
        }

        return $value;
    }

    /**
     * 
     * @param string $configKey Key to access app.php data.
     * 
     * Exemple: Configure::read('App') or Configure::read('App/site/theme')
     * 
     * @return null | mixed
     */
    public static function read($configKey)
    {
        $keys = explode('/', $configKey);
        $result = null;

        foreach ($keys as $i => $key) {

            if (isset($result[$key])) {
                $result = $result[$key];
            }
            else if (isset(self::$config[$key])) {
                $result = self::$config[$key];
            }
        }

        return $result;
    }

}
