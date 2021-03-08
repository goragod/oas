<?php

/**
 * @filesource Kotchasan/Kotchasan.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */
use Kotchasan\Config;
use Kotchasan\Http\Request;

/**
 * Kotchasan PHP Framework.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Kotchasan extends Kotchasan\KBase
{
    /**
     * default charset (แนะนำ utf-8).
     *
     * @var string
     */
    public $char_set = 'utf-8';
    /**
     * Controller หลัก
     *
     * @var string
     */
    public $defaultController = 'Index\Index\Controller';
    /**
     * Router หลัก
     *
     * @var string
     */
    public $defaultRouter = 'Kotchasan\Router';
    /**
     * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
     */
    private static $instance = null;

    /**
     * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น.
     *
     * @param Config|string|null $cfg ถ้าไม่กำหนดมาจะใช้ค่าเริ่มต้นของคชสาร
     *
     * @return \static
     */
    public static function createWebApplication($cfg = null)
    {
        if (null === self::$instance) {
            self::$instance = new static($cfg);
        }

        return self::$instance;
    }

    /**
     * แสดงผลหน้าเว็บไซต์.
     */
    public function run()
    {
        $router = new $this->defaultRouter();
        $router->init($this->defaultController);
    }

    /**
     * create Singleton.
     *
     * @param Config|string|null $cfg
     */
    private function __construct($cfg)
    {
        /* Request Class with Apache HTTP headers */
        self::$request = new Request(true);
        /* config */
        if (empty($cfg)) {
            self::$cfg = Config::create();
        } elseif (is_string($cfg)) {
            self::$cfg = $cfg::create();
        } else {
            self::$cfg = $cfg;
        }
        /* charset */
        ini_set('default_charset', $this->char_set);
        if (extension_loaded('mbstring')) {
            mb_internal_encoding($this->char_set);
        }
        /* time zone */
        @date_default_timezone_set(self::$cfg->timezone);
        /* custom init site */
        if (is_string($cfg) && method_exists($cfg, 'init')) {
            $cfg::init(self::$cfg);
        }
    }
}
