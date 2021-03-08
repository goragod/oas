<?php
/**
 * load.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */
/*
 * Site root
 */
define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)).'/');
/*
 * โฟลเดอร์เก็บข้อมูล
 */
define('DATA_FOLDER', 'datas/');
/*
 * 0 (default) บันทึกเฉพาะข้อผิดพลาดร้ายแรงลง error_log .php
 * 1 บันทึกข้อผิดพลาดและคำเตือนลง error_log .php
 * 2 แสดงผลข้อผิดพลาดและคำเตือนออกทางหน้าจอ (ใช้เฉพาะตอนออกแบบเท่านั้น)
 */
define('DEBUG', 0);
/*
 * false (default)
 * true บันทึกการ query ฐานข้อมูลลง log (ใช้เฉพาะตอนออกแบบเท่านั้น)
 */
define('DB_LOG', false);
/*
 * ภาษาอัตโนมัติจากบราวเซอร์
 */
define('AUTO_LANGUAGE', false);
/**
 * เปิด/ปิดการใช้งาน Session บน Database
 * ต้องติดตั้งตาราง sessions ด้วย
 */
define('USE_SESSION_DATABASE', false);
/*
 * ระบุ SQL Mode ที่ต้องการ
 * หากพบปัญหาการใช้งาน
 */
//define('SQL_MODE', '');
/**
 * load Kotchasan.
 */
include 'Kotchasan/load.php';
