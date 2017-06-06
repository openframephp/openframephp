<?php

namespace OpenFrame\Core;

/**
 *
 * @author Leandro de Amorim <androrim@gmail.com>
 */
class Request
{

    public static $routes;
    public static $protocol;

    public static function init($routesJson)
    {
        self::$routes = json_decode($routesJson, true);
        self::$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
    }

    public static function get($name = null)
    {
        $baseUri = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $params = str_replace($baseUri, '', $_SERVER['REQUEST_URI']);

        $result = self::buildRequest($params, array(
                    'page' => Configure::read('App/site/home'),
                    'params' => array(),
                    'status' => 200
        ));

        if ($name && isset($result[$name])) {
            return $result[$name];
        }
        
        return $result;
    }

    private static function buildRequest($params, $result)
    {
        $arrayRequest = array_filter(explode('/', $params));
        $requested = null;
        
        if ($params === '') {
            return $result;
        }

        foreach ($arrayRequest as $i => $param) {
            if (is_null($requested)) {
                $requested = $param;
            }
            else {
                $requested .= DIRECTORY_SEPARATOR . $param;
            }

            if (isset(self::$routes[$requested])) {
                $routeParams = array_slice($arrayRequest, $i + 1);
                $result['page'] = $requested;
                $result['params'] = self::buildRequestParams(self::$routes[$requested], $routeParams);
                $result['status'] = self::checkRequestStatus($requested);
                break;
            }

            $result['page'] = $requested;
            $result['status'] = self::checkRequestStatus($requested);
        }
        
        return $result;
    }

    private static function buildRequestParams($route, $params)
    {
        $result = array();

        foreach ($route as $i => $param) {
            $result[$param] = null;

            if (isset($params[$i])) {
                $result[$param] = $params[$i];
            }
        }

        return $result;
    }

    private static function checkRequestStatus($request)
    {
        $status = 404;
        $pagesPath = App::pagesPath();

        if (file_exists("{$pagesPath}/{$request}/index.php")) {
            $status = 200;
        }

        return $status;
    }

}
