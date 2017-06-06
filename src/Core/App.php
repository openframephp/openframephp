<?php

namespace OpenFrame\Core;

/**
 *
 * @author Leandro de Amorim <androrim@gmail.com>
 */
class App
{

    private static $pagesPath;

    public static function init($confDir)
    {
        Configure::init(require $confDir . '/app.php');

        if (Configure::read('App/debug')) {
            ini_set("display_errors", "1");
            error_reporting(E_ALL);
        }

        Request::init(file_get_contents($confDir . '/routes.json'));
        
        var_dump(Request::get());

        self::loadResquestedPage();
    }

    public static function pagesPath()
    {
        self::$pagesPath = Configure::read('App/base')
                . DIRECTORY_SEPARATOR . Configure::read('App/dir')
                . DIRECTORY_SEPARATOR . Configure::read('App/site/theme')
                . DIRECTORY_SEPARATOR . Configure::read('App/site/pages');

        return self::$pagesPath;
    }

    public static function baseUrl($path = null, $protocol = null)
    {
        $domain = $_SERVER['REMOTE_ADDR'];
        $path = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        
        if ($path) {
            return Request::$protocol . $domain . $path . '/' . $path;
        }
        
        return Request::$protocol . $domain . $path;
    }

    private static function loadResquestedPage()
    {
        $requested = Request::get();
        $errorPage = Configure::read('App/site/error');
        
        http_response_code($requested['status']);
        
        // TODO checar demais status codes

        if ($requested['status'] === 200) {
            self::loadPage($requested['page']);
        }
        else if ($requested['status'] === 404 && !$errorPage) {
            self::loadPage(Configure::read('App/site/home'));
        }
        else if ($requested['status'] === 404 && $errorPage) {
            self::loadPage(Configure::read('App/site/error'), $requested['status']);
        }
        else {
            self::loadPage(Configure::read('App/site/home'));
        }
    }

    public static function loadPage($name, $file = null)
    {
        $pagesPath = self::pagesPath();
        $_name = str_replace('.php', '', $name);
        $_file = str_replace('.php', '', $file);
        
        if (file_exists("{$pagesPath}/{$_name}/index.php")) {
            include "{$pagesPath}/{$_name}/index.php";
        }
        else if(file_exists("{$pagesPath}/{$_name}.php")) {
            include "{$pagesPath}/{$_name}.php";
        }
        else if ($file && file_exists("{$pagesPath}/{$_name}/{$_file}.php")) {
            include "{$pagesPath}/{$_name}/{$_file}.php";
        }
    }
    
    public static function isHome()
    {
        return Request::get('page') === Configure::read('App/site/home');
    }

}
