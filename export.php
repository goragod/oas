<?php
/**
 * export.php
 * หน้าเว็บสำหรับการเข้าถึง Class แบบที่ไม่ผ่านกระบวนการ Login และการโหลด Template
 * เช่นหน้าสำหรับพิมพ์ หรือการดาวน์โหลดไฟล์.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */
// load Kotchasan
include 'load.php';
// Initial Kotchasan Framework
$app = Kotchasan::createWebApplication('Gcms\Config');
$app->defaultController = 'Index\Export\Controller';
$app->run();
