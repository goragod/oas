<?php
/**
 * @filesource Kotchasan/load.php
 *
 * ไฟล์หลักสำหรับกำหนดค่าเริ่มต้นในการโหลดเฟรมเวิร์ค
 * ต้อง include ไฟล์นี้ก่อนเสมอ
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

/**
 * การแสดงข้อผิดพลาด
 * 0 บันทึกข้อผิดพลาดร้ายแรงลง error_log .php (ขณะใช้งานจริง)
 * 1 บันทึกข้อผิดพลาดและคำเตือนลง error_log .php
 * 2 แสดงผลข้อผิดพลาดและคำเตือนออกทางหน้าจอ (เฉพาะตอนออกแบบเท่านั้น).
 *
 * @var int
 */
if (!defined('DEBUG')) {
    define('DEBUG', 0);
}
/* display error */
if (DEBUG > 0) {
    /* ขณะออกแบบ แสดง error และ warning ของ PHP */
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
} else {
    /* ขณะใช้งานจริง */
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}
/*
 * Framework Version
 *
 * @var string
 */
define('VERSION', '3.5.0');
/*
 * กำหนดการบันทึกการ query ฐานข้อมูล
 * ควรกำหนดเป็น false ขณะใช้งานจริง
 *
 * $var bool
 */
if (!defined('DB_LOG')) {
    define('DB_LOG', false);
}
/**
 * ไดเรคทอรี่ของ Framework.
 */
$vendorDir = str_replace('load.php', '', __FILE__);
if (DIRECTORY_SEPARATOR != '/') {
    $vendorDir = str_replace('\\', '/', $vendorDir);
}
define('VENDOR_DIR', $vendorDir);
/*
 * พาธของ Server ตั้งแต่ระดับราก เช่น D:/htdocs/Somtum/
 */
$docRoot = dirname($vendorDir);
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $docRoot.'/');
}
/**
 *  document root (Server).
 */
$contextPrefix = '';
if (isset($_SERVER['APPL_PHYSICAL_PATH'])) {
    $docRoot = rtrim(realpath($_SERVER['APPL_PHYSICAL_PATH']), DIRECTORY_SEPARATOR);
    if (DIRECTORY_SEPARATOR != '/' && $docRoot != '') {
        $docRoot = str_replace('\\', '/', $docRoot);
    }
} elseif (strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) !== false) {
    $docRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DIRECTORY_SEPARATOR);
    if (DIRECTORY_SEPARATOR != '/' && $docRoot != '') {
        $docRoot = str_replace('\\', '/', $docRoot);
    }
} else {
    $dir = basename($docRoot);
    $ds = explode($dir, dirname($_SERVER['SCRIPT_NAME']), 2);
    if (count($ds) > 1) {
        $contextPrefix = $ds[0].$dir;
        $appPath = $ds[1];
        if (DIRECTORY_SEPARATOR != '/') {
            $contextPrefix = str_replace('\\', '/', $contextPrefix);
        }
    }
    if (!defined('APP_PATH')) {
        define('APP_PATH', $docRoot.$appPath.'/');
    }
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', $contextPrefix.$appPath.'/');
    }
}

/*
 * พาธของ Application เช่น D:/htdocs/kotchasan/
 */
if (!defined('APP_PATH')) {
    $appPath = dirname($_SERVER['SCRIPT_NAME']);
    if (DIRECTORY_SEPARATOR != '/') {
        $appPath = str_replace('\\', '/', $appPath);
    }
    define('APP_PATH', rtrim($docRoot.$appPath, '/').'/');
}
/*
 *  http หรือ https
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://';
} elseif ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
    $scheme = 'https://';
} else {
    $scheme = 'http://';
}
if (!defined('HTTPS')) {
    define('HTTPS', $scheme == 'https://');
}
/*
 * host name
 */
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $host = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])));
} elseif (empty($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['SERVER_NAME'];
} else {
    $host = $_SERVER['HTTP_HOST'];
}
if (!defined('HOST')) {
    define('HOST', str_replace('www.', '', $host));
}
/*
 * ไดเร็คทอรี่ที่ติดตั้งเว็บไซต์ตั้งแต่ DOCUMENT_ROOT
 * เช่น kotchasan/
 */
if (!defined('BASE_PATH')) {
    if (empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
        define('BASE_PATH', str_replace($docRoot, '', APP_PATH));
    } else {
        $basePath = str_replace($_SERVER['CONTEXT_DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_NAME']));
        if (DIRECTORY_SEPARATOR != '/') {
            $basePath = str_replace('\\', '/', $basePath);
        }
        define('BASE_PATH', rtrim($basePath, '/').'/');
    }
}
/*
 * URL ของเว็บไซต์รวม path เช่น http://domain.tld/folder
 */
if (!defined('WEB_URL')) {
    define('WEB_URL', $scheme.$host.$contextPrefix.str_replace($docRoot, '', ROOT_PATH));
}
/*
 * กำหนดจำนวนครั้งในการตรวจสอบ token
 * ถ้ามีการตรวจสอบ token เกินกว่าที่กำหนดจะถูกลบออก
 * ป้องกันการ buteforce
 *
 * @var int
 */
if (!defined('TOKEN_LIMIT')) {
    define('TOKEN_LIMIT', 10);
}
/*
 * อายุของ token (วินาที)
 * ค่าเริ่มต้นคือ 60 นาที
 *
 * @var int
 */
if (!defined('TOKEN_AGE')) {
    define('TOKEN_AGE', 3600);
}

/**
 * ฟังก์ชั่นใช้สำหรับสร้างคลาส.
 *
 * @param string $className ชื่อคลาส
 * @param mixed  $param
 *
 * @return object
 */
function createClass($className, $param = null)
{
    return new $className($param);
}

/**
 * แสดงข้อมูลตัวแปรออกทาง Console ของบราวเวอร์ (debug).
 *
 * @param mixed $expression
 */
function debug($expression)
{
    if (is_array($expression) || is_object($expression)) {
        $content = json_encode((array) $expression);
    } else {
        $content = '"'.str_replace(array('/', '"'), array('\/', '\"'), $expression).'"';
    }
    echo '<script>console.log('.$content.')</script>';
}
/*
 * custom error handler
 * ถ้าอยู่ใน mode debug จะแสดง error ถ้าไม่จะเขียนลง log อย่างเดียว
 */
if (DEBUG != 2) {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_WARNING:
                $type = 'PHP warning';
                break;
            case E_NOTICE:
                $type = 'PHP notice';
                break;
            case E_USER_ERROR:
                $type = 'User error';
                break;
            case E_USER_WARNING:
                $type = 'User warning';
                break;
            case E_USER_NOTICE:
                $type = 'User notice';
                break;
            case E_RECOVERABLE_ERROR:
                $type = 'Recoverable error';
                break;
            default:
                $type = 'PHP Error';
        }
        \Kotchasan\Log\Logger::create()->error('<br>'.$type.' : <em>'.$errstr.'</em> in <b>'.$errfile.'</b> on line <b>'.$errline.'</b>');
    });
    set_exception_handler(function ($e) {
        $tract = $e->getTrace();
        if (empty($tract)) {
            $tract = array(
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            );
        } else {
            $tract = next($tract);
        }
        \Kotchasan\Log\Logger::create()->error('<br>Exception : <em>'.$e->getMessage().'</em> in <b>'.$tract['file'].'</b> on line <b>'.$tract['line'].'</b>');
    });
}

/**
 * ตรวจสอบและคืนค่าชื่อไฟล์รวมพาธของคลาส.
 *
 * @param string $className
 *
 * @return string|null คืนค่าไฟล์รวมพาธของคลาส ถ้าไม่พบคืนค่า null
 */
function getClassPath($className)
{
    if (preg_match_all('/([\/\\])([a-zA-Z0-9]+)/', $className, $match)) {
        $className = implode(DIRECTORY_SEPARATOR, $match[1]).'.php';
        if (is_file(ROOT_PATH.$className)) {
            return ROOT_PATH.$className;
        } elseif (isset($match[1][2])) {
            if (isset($match[1][3])) {
                $className = strtolower('modules'.DIRECTORY_SEPARATOR.$match[1][0].DIRECTORY_SEPARATOR.$match[1][3].'s'.DIRECTORY_SEPARATOR.$match[1][1].DIRECTORY_SEPARATOR.$match[1][2].'.php');
            } else {
                $className = strtolower('modules'.DIRECTORY_SEPARATOR.$match[1][0].DIRECTORY_SEPARATOR.$match[1][2].'s'.DIRECTORY_SEPARATOR.$match[1][1].'.php');
            }
            if (is_file(APP_PATH.$className)) {
                return APP_PATH.$className;
            } elseif (is_file(ROOT_PATH.$className)) {
                return ROOT_PATH.$className;
            }
        }
    }

    return null;
}
/*
 * โหลดคลาสโดยอัตโนมัติตามชื่อของ Classname เมื่อมีการเรียกใช้งานคลาส
 * PSR-4
 *
 * @param string $className
 */
spl_autoload_register(function ($className) {
    $files = array(
        'Kotchasan\Cache\ApcCache' => 'Cache/ApcCache.php',
        'Kotchasan\Cache\Cache' => 'Cache/Cache.php',
        'Kotchasan\Cache\CacheItem' => 'Cache/CacheItem.php',
        'Kotchasan\Cache\Exception' => 'Cache/Exception.php',
        'Kotchasan\Cache\FileCache' => 'Cache/FileCache.php',
        'Kotchasan\Database\Db' => 'Database/Db.php',
        'Kotchasan\Database\DbCache' => 'Database/DbCache.php',
        'Kotchasan\Database\Driver' => 'Database/Driver.php',
        'Kotchasan\Database\Exception' => 'Database/Exception.php',
        'Kotchasan\Database\PdoMysqlDriver' => 'Database/PdoMysqlDriver.php',
        'Kotchasan\Database\Query' => 'Database/Query.php',
        'Kotchasan\Database\QueryBuilder' => 'Database/QueryBuilder.php',
        'Kotchasan\Database\Schema' => 'Database/Schema.php',
        'Kotchasan\Database\Sql' => 'Database/Sql.php',
        'Kotchasan\Http\Message' => 'Http/Message.php',
        'Kotchasan\Http\NotFound' => 'Http/NotFound.php',
        'Kotchasan\Http\Response' => 'Http/Response.php',
        'Kotchasan\Http\Stream' => 'Http/Stream.php',
        'Kotchasan\Http\UploadedFile' => 'Http/UploadedFile.php',
        'Kotchasan\Log\AbstractLogger' => 'Log/AbstractLogger.php',
        'Kotchasan\Log\Logger' => 'Log/Logger.php',
        'Kotchasan\Orm\Field' => 'Orm/Field.php',
        'Kotchasan\Orm\Recordset' => 'Orm/Recordset.php',
        'Kotchasan\PHPMailer\class' => 'PHPMailer/class.php',
        'Kotchasan\PHPMailer\class.smtp' => 'PHPMailer/class.smtp.php',
        'Psr\Cache\CacheItemInterface' => 'Psr/Cache/CacheItemInterface.php',
        'Psr\Cache\CacheItemPoolInterface' => 'Psr/Cache/CacheItemPoolInterface.php',
        'Psr\Http\Message\ResponseInterface' => 'Psr/Http/Message/ResponseInterface.php',
        'Psr\Http\Message\ServerRequestInterface' => 'Psr/Http/Message/ServerRequestInterface.php',
        'Psr\Http\Message\StreamInterface' => 'Psr/Http/Message/StreamInterface.php',
        'Psr\Http\Message\UploadedFileInterface' => 'Psr/Http/Message/UploadedFileInterface.php',
        'Psr\Log\AbstractLogger' => 'Psr/Log/AbstractLogger.php',
        'Psr\Log\LogLevel' => 'Psr/Log/LogLevel.php',
        'Psr\Log\LoggerAwareInterface' => 'Psr/Log/LoggerAwareInterface.php',
        'Psr\Log\LoggerInterface' => 'Psr/Log/LoggerInterface.php',
        'Psr\Log\LoggerTrait' => 'Psr/Log/LoggerTrait.php',
        'Psr\Log\NullLogger' => 'Psr/Log/NullLogger.php',
        'Kotchasan\Accordion' => 'Accordion.php',
        'Kotchasan\ArrayTool' => 'ArrayTool.php',
        'Kotchasan\CKEditor' => 'CKEditor.php',
        'Kotchasan\Collection' => 'Collection.php',
        'Kotchasan\Country' => 'Country.php',
        'Kotchasan\Csv' => 'Csv.php',
        'Kotchasan\Curl' => 'Curl.php',
        'Kotchasan\Currency' => 'Currency.php',
        'Kotchasan\DOMNode' => 'DOMNode.php',
        'Kotchasan\DOMParser' => 'DOMParser.php',
        'Kotchasan\DataTable' => 'DataTable.php',
        'Kotchasan\Database' => 'Database.php',
        'Kotchasan\Date' => 'Date.php',
        'Kotchasan\Email' => 'Email.php',
        'Kotchasan\File' => 'File.php',
        'Kotchasan\Files' => 'Files.php',
        'Kotchasan\Form' => 'Form.php',
        'Kotchasan\Grid' => 'Grid.php',
        'Kotchasan\Html' => 'Html.php',
        'Kotchasan\HtmlTable' => 'HtmlTable.php',
        'Kotchasan\Htmldoc' => 'Htmldoc.php',
        'Kotchasan\Image' => 'Image.php',
        'Kotchasan\InputItem' => 'InputItem.php',
        'Kotchasan\Inputs' => 'Inputs.php',
        'Kotchasan\Language' => 'Language.php',
        'Kotchasan\ListItem' => 'ListItem.php',
        'Kotchasan\Login' => 'Login.php',
        'Kotchasan\Menu' => 'Menu.php',
        'Kotchasan\Mime' => 'Mime.php',
        'Kotchasan\Model' => 'Model.php',
        'Kotchasan\Number' => 'Number.php',
        'Kotchasan\Password' => 'Password.php',
        'Kotchasan\Pdf' => 'Pdf.php',
        'Kotchasan\Province' => 'Province.php',
        'Kotchasan\Singleton' => 'Singleton.php',
        'Kotchasan\Tab' => 'Tab.php',
        'Kotchasan\Template' => 'Template.php',
        'Kotchasan\Text' => 'Text.php',
        'Kotchasan\Validator' => 'Validator.php',
        'Kotchasan\View' => 'View.php',
        'Kotchasan\load' => 'load.php',
        'Kotchasan\ObjectTool' => 'ObjectTool.php',
        'Kotchasan\InputItemException' => 'InputItemException.php',
    );
    if (isset($files[$className])) {
        $file = VENDOR_DIR.$files[$className];
    } else {
        $file = getClassPath($className);
    }
    if ($file !== null) {
        require $file;
    }
});

/**
 * โหลดคลาสเริ่มต้น
 */
require VENDOR_DIR.'KBase.php';
require VENDOR_DIR.'Config.php';
require VENDOR_DIR.'Psr/Http/Message/MessageInterface.php';
require VENDOR_DIR.'Psr/Http/Message/RequestInterface.php';
require VENDOR_DIR.'Psr/Http/Message/UriInterface.php';
require VENDOR_DIR.'Http/AbstractMessage.php';
require VENDOR_DIR.'Http/AbstractRequest.php';
require VENDOR_DIR.'Http/Request.php';
require VENDOR_DIR.'Http/Uri.php';
require VENDOR_DIR.'Router.php';
require VENDOR_DIR.'Kotchasan.php';
require VENDOR_DIR.'Controller.php';
