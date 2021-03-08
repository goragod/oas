<?php

if (defined('ROOT_PATH')) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        include ROOT_PATH.'install/upgrade1.php';
    } else {
        $error = false;
        // Database Class
        include ROOT_PATH.'install/db.php';
        // ค่าติดตั้งฐานข้อมูล
        $db_config = include ROOT_PATH.'settings/database.php';
        try {
            $db_config = $db_config['mysql'];
            // เขื่อมต่อฐานข้อมูล
            $db = new Db($db_config);
        } catch (\Exception $e) {
            $error = true;
            echo '<h2>ความผิดพลาดในการเชื่อมต่อกับฐานข้อมูล</h2>';
            echo '<p class=warning>ไม่สามารถเชื่อมต่อกับฐานข้อมูลของคุณได้ในขณะนี้</p>';
            echo '<p>อาจเป็นไปได้ว่า</p>';
            echo '<ol>';
            echo '<li>เซิร์ฟเวอร์ของฐานข้อมูลของคุณไม่สามารถใช้งานได้ในขณะนี้</li>';
            echo '<li>ค่ากำหนดของฐานข้อมูลไม่ถูกต้อง (ตรวจสอบไฟล์ settings/database.php)</li>';
            echo '<li>ไม่พบฐานข้อมูลที่ต้องการติดตั้ง กรุณาสร้างฐานข้อมูลก่อน หรือใช้ฐานข้อมูลที่มีอยู่แล้ว</li>';
            echo '<li class="incorrect">'.$e->getMessage().'</li>';
            echo '</ol>';
            echo '<p>หากคุณไม่สามารถดำเนินการแก้ไขข้อผิดพลาดด้วยตัวของคุณเองได้ ให้ติดต่อผู้ดูแลระบบเพื่อขอข้อมูลที่ถูกต้อง หรือ ลองติดตั้งใหม่</p>';
            echo '<p><a href="index.php?step=1" class="button large pink">กลับไปลองใหม่</a></p>';
        }
        if (!$error) {
            // เชื่อมต่อฐานข้อมูลสำเร็จ
            $content = array('<li class="correct">เชื่อมต่อฐานข้อมูลสำเร็จ</li>');
            try {
                // ตาราง user
                $table = $db_config['prefix'].'_user';
                if (empty($config['password_key'])) {
                    // อัปเดตข้อมูลผู้ดูแลระบบ
                    $config['password_key'] = uniqid();
                }
                // ตรวจสอบการ login
                updateAdmin($db, $table, $_POST['username'], $_POST['password'], $config['password_key']);
                if (!$db->fieldExists($table, 'social')) {
                    $db->query("ALTER TABLE `$table` CHANGE `fb` `social` TINYINT(1) NOT NULL DEFAULT 0");
                }
                if (!$db->fieldExists($table, 'country')) {
                    $db->query("ALTER TABLE `$table` ADD `country` VARCHAR(2)");
                }
                if (!$db->fieldExists($table, 'province')) {
                    $db->query("ALTER TABLE `$table` ADD `province` VARCHAR(50)");
                }
                if (!$db->fieldExists($table, 'token')) {
                    $db->query("ALTER TABLE `$table` ADD `token` VARCHAR(50) NULL AFTER `password`");
                }
                $db->query("ALTER TABLE `$table` CHANGE `address` `address` VARCHAR(150) DEFAULT NULL");
                $db->query("ALTER TABLE `$table` CHANGE `password` `password` VARCHAR(50) NOT NULL");
                if (!$db->indexExists($table, 'phone')) {
                    $db->query("ALTER TABLE `$table` ADD INDEX (`phone`)");
                }
                if (!$db->indexExists($table, 'token')) {
                    $db->query("ALTER TABLE `$table` ADD INDEX (`token`)");
                }
                if ($db->indexExists($table, 'id_card')) {
                    $db->query("ALTER TABLE `$table` DROP INDEX `id_card`");
                }
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table.'` สำเร็จ</li>';
                // ตาราง category
                $table_category = $db_config['prefix'].'_category';
                // ตาราง inventory, inventory_items,stock
                $table_product = $db_config['prefix'].'_product';
                $table_inventory = $db_config['prefix'].'_inventory';
                $table_items = $db_config['prefix'].'_inventory_items';
                $table_stock = $db_config['prefix'].'_stock';
                $table_price = $db_config['prefix'].'_inventory_price';
                // มีตาราง price
                $hasTablePrice = $db->tableExists($table_price);
                if (!$db->tableExists($table_inventory) && $db->tableExists($table_product)) {
                    $db->query("RENAME TABLE `$table_product` TO `$table_inventory`");
                }
                if ($db->fieldExists($table_inventory, 'create_date')) {
                    $db->query("ALTER TABLE `$table_inventory` CHANGE `create_date` `create_date` DATE DEFAULT NULL");
                } else {
                    $db->query("ALTER TABLE `$table_inventory` ADD `create_date` DATE DEFAULT NULL");
                }
                if (!$db->fieldExists($table_inventory, 'cost')) {
                    $db->query("ALTER TABLE `$table_inventory` ADD `cost` FLOAT DEFAULT 0");
                }
                if (!$db->fieldExists($table_inventory, 'stock')) {
                    $db->query("ALTER TABLE `$table_inventory` ADD `stock` FLOAT DEFAULT 0");
                }
                if ($db->fieldExists($table_items, 'stock')) {
                    $db->query("UPDATE `$table_inventory` AS S SET `stock`=(SELECT `stock` FROM `$table_items` AS I WHERE I.`inventory_id`=S.`id`)");
                }
                if ($db->fieldExists($table_inventory, 'unit')) {
                    if ($db->isColumnType($table_inventory, 'unit', 'INT')) {
                        $db->query("ALTER TABLE `$table_inventory` CHANGE `unit` `unit` VARCHAR(50) DEFAULT NULL");
                        $db->query("UPDATE `$table_inventory` AS I SET `unit`=(SELECT C.`topic` FROM `$table_category` AS C WHERE C.`category_id`=I.`unit` AND C.`type`='unit')");
                    }
                } else {
                    $db->query("ALTER TABLE `$table_inventory` ADD `unit` VARCHAR(50) DEFAULT NULL");
                    if ($hasTablePrice) {
                        $db->query("UPDATE `$table_inventory` AS I SET `unit`=(SELECT C.`topic` FROM `$table_price` AS P INNER JOIN `$table_category` AS C ON C.`category_id`=P.`unit` AND C.`type`='unit' WHERE P.`inventory_id`=I.`id`)");
                    }
                }
                // ตาราง inventory_meta
                $table_meta = $db_config['prefix'].'_inventory_meta';
                if (!$db->tableExists($table)) {
                    $db->query("CREATE TABLE `$table_meta` (`inventory_id` int(11) NOT NULL,`name` varchar(20) NOT NULL,`value` TEXT NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8");
                    $db->query("ALTER TABLE `$table_meta` ADD KEY `inventory_id` (`inventory_id`)");
                } else {
                    $db->query("ALTER TABLE `$table_meta` CHANGE `value` `value` TEXT NOT NULL");
                }
                if ($db->fieldExists($table_inventory, 'description')) {
                    $db->query("INSERT INTO `$table_meta` (`inventory_id`,`name`,`value`) SELECT `id`,'description',`description` FROM `$table_inventory` WHERE `description`!=''");
                    $db->query("ALTER TABLE `$table_inventory` DROP `description`");
                }
                if ($db->fieldExists($table_inventory, 'detail')) {
                    $db->query("INSERT INTO `$table_meta` (`inventory_id`,`name`,`value`) SELECT `id`,'detail',`detail` FROM `$table_inventory` WHERE `detail`!=''");
                    $db->query("ALTER TABLE `$table_inventory` DROP `detail`");
                }
                if ($db->fieldExists($table_inventory, 'url')) {
                    $db->query("INSERT INTO `$table_meta` (`inventory_id`,`name`,`value`) SELECT `id`,'url',`url` FROM `$table_inventory` WHERE `url`!=''");
                    $db->query("ALTER TABLE `$table_inventory` DROP `url`");
                }
                if ($db->fieldExists($table_inventory, 'image')) {
                    $db->query("INSERT INTO `$table_meta` (`inventory_id`,`name`,`value`) SELECT `id`,'image',`image` FROM `$table_inventory` WHERE `image`!=''");
                    $db->query("ALTER TABLE `$table_inventory` DROP `image`");
                }
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table_meta.'` สำเร็จ</li>';
                if (!$db->tableExists($table_items)) {
                    $db->query("CREATE TABLE `$table_items` LIKE `$table_inventory`");
                    $db->query("INSERT `$table_items` SELECT * FROM `$table_inventory`");
                    $db->query("ALTER TABLE `$table_items` ADD `inventory_id` INT NOT NULL AFTER `product_no`, ADD INDEX (`inventory_id`)");
                    $db->query("UPDATE `$table_items` SET `inventory_id`=`id`");
                    $db->query("ALTER TABLE `$table_items` DROP `description`,DROP `vat`,DROP `count_stock`,DROP `category_id`");
                    $content[] = '<li class="correct">สร้างตาราง `'.$table_items.'` สำเร็จ</li>';
                }
                if (!$db->fieldExists($table_items, 'topic')) {
                    $db->query("ALTER TABLE `$table_items` ADD `topic` VARCHAR(100) NOT NULL");
                }
                if (!$db->fieldExists($table_items, 'price')) {
                    $db->query("ALTER TABLE `$table_items` ADD `price` FLOAT DEFAULT 0");
                    if ($hasTablePrice) {
                        $db->query("UPDATE `$table_items` AS I SET `price`=(SELECT `price` FROM `$table_price` AS P WHERE P.`inventory_id`=I.`inventory_id`)");
                    } elseif ($db->fieldExists($table_inventory, 'price')) {
                        $db->query("UPDATE `$table_items` AS I SET `price`=(SELECT `price` FROM `$table_inventory` AS V WHERE V.`id`=I.`inventory_id`)");
                    }
                }
                if (!$db->fieldExists($table_items, 'cut_stock')) {
                    $db->query("ALTER TABLE `$table_items` ADD `cut_stock` FLOAT DEFAULT 1");
                }
                if (!$db->fieldExists($table_items, 'unit')) {
                    $db->query("ALTER TABLE `$table_items` ADD `unit` VARCHAR(50) DEFAULT NULL");
                    $db->query("UPDATE `$table_items` AS I SET `unit`=(SELECT V.`unit` FROM `$table_inventory` AS V WHERE V.`id`=I.`inventory_id`)");
                }
                if (!$db->fieldExists($table_items, 'instock')) {
                    $db->query("ALTER TABLE `$table_items` ADD `instock` TINYINT(1) DEFAULT 0");
                }
                if ($db->fieldExists($table_items, 'stock')) {
                    $db->query("UPDATE `$table_items` AS I SET `instock`=(CASE WHEN `stock`=0 THEN 0 ELSE 1 END)");
                    $db->query("ALTER TABLE `$table_items` DROP `stock`");
                }
                if ($db->fieldExists($table_stock, 'item_id')) {
                    $db->query("ALTER TABLE `$table_stock` CHANGE `item_id` `inventory_id` INT(11)");
                    if (!$db->tableExists($table_items)) {
                        $db->query("UPDATE `$table_stock` AS S SET `inventory_id`=(SELECT `inventory_id` FROM `$table_items` AS I WHERE I.`id`=S.`inventory_id`)");
                    }
                }
                $db->query("ALTER TABLE `$table_stock` CHANGE `status` `status` ENUM('IN','OUT','PO','QUO','RET')");
                if (!$db->fieldExists($table_stock, 'product_no')) {
                    $db->query("ALTER TABLE `$table_stock` ADD `product_no` VARCHAR(50) NULL AFTER `inventory_id`, ADD INDEX (`product_no`)");
                    $db->query("UPDATE `$table_stock` AS S SET `product_no`=(SELECT `product_no` FROM `$table_items` AS I WHERE I.`inventory_id`=S.`inventory_id`)");
                    $db->query("ALTER TABLE `$table_stock` CHANGE `unit` `unit` VARCHAR(50)");
                    $db->query("UPDATE `$table_stock` AS S SET `unit`=(SELECT `unit` FROM `$table_inventory` AS V WHERE V.`id`=S.`inventory_id`)");
                }
                if (!$db->fieldExists($table_stock, 'unit')) {
                    $db->query("ALTER TABLE `$table_stock` ADD `unit` VARCHAR(50) DEFAULT NULL");
                    $db->query("UPDATE `$table_stock` AS S SET `unit`=(SELECT `unit` FROM `$table_inventory` AS V WHERE V.`id`=S.`inventory_id`)");
                }
                if (!$db->fieldExists($table_stock, 'cut_stock')) {
                    $db->query("ALTER TABLE `$table_stock` ADD `cut_stock` FLOAT NULL AFTER `quantity`");
                    $db->query("UPDATE `$table_stock` AS S SET `cut_stock`=1");
                }
                $db->query("ALTER TABLE `$table_stock` CHANGE `quantity` `quantity` FLOAT DEFAULT 0");
                if ($db->fieldExists($table_stock, 'order_status') && $db->fieldExists($table_stock, 'status')) {
                    $db->query("ALTER TABLE `$table_stock`DROP `status`");
                    $db->query("ALTER TABLE `$table_stock` CHANGE `order_status` `status` ENUM('IN','OUT','PO','QUO','RET')");
                } else {
                    $db->query("ALTER TABLE `$table_stock` CHANGE `status` `status` ENUM('IN','OUT','PO','QUO','RET')");
                }
                if (!$db->indexExists($table_stock, 'status')) {
                    $db->query("ALTER TABLE `$table_stock` ADD INDEX (`status`)");
                }
                if ($db->fieldExists($table_items, 'id')) {
                    $db->query("ALTER TABLE `$table_items` ADD `product_no2` VARCHAR(150)");
                    $db->query("UPDATE `$table_items` SET `product_no2`=`product_no`");
                    $db->query("ALTER TABLE `$table_items` DROP `product_no`");
                    $db->query("ALTER TABLE `$table_items` CHANGE `product_no2` `product_no` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL FIRST");
                    $db->query("ALTER TABLE `$table_items` DROP `id`,DROP `create_date`");
                    $db->query("ALTER TABLE `$table_items` ADD PRIMARY KEY (`product_no`)");
                }
                $db->query("ALTER TABLE `$table_stock` ADD INDEX (`inventory_id`)");
                if ($hasTablePrice) {
                    $db->query("DROP TABLE `$table_price`");
                }
                $db->query("DELETE FROM `$table_category` WHERE `type`='unit'");
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table_category.'` สำเร็จ</li>';
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table_inventory.'` สำเร็จ</li>';
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table_stock.'` สำเร็จ</li>';
                // ตาราง orders
                $table = $db_config['prefix'].'_orders';
                $db->query("ALTER TABLE `$table` CHANGE `status` `status` ENUM('IN','OUT','PO','QUO','RET') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
                $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table.'` สำเร็จ</li>';
                // ตาราง number
                $table = $db_config['prefix'].'_number';
                if (!$db->fieldExists($table, 'auto_increment')) {
                    $db->query("ALTER TABLE `$table` ADD `auto_increment` INT NOT NULL, ADD `last_update` DATE DEFAULT NULL");
                    $db->query("ALTER TABLE `$table` CHANGE `id` `type` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
                    if ($db->fieldExists($table, 'IN_NO')) {
                        $db->query("INSERT INTO `$table` (`type`, `auto_increment`, `last_update`) SELECT 'IN_NO', `IN_NO`, NULL FROM `$table` WHERE `type`='1' LIMIT 1");
                    }
                    if ($db->fieldExists($table, 'OUT_NO')) {
                        $db->query("INSERT INTO `$table` (`type`, `auto_increment`, `last_update`) SELECT 'OUT_NO', `OUT_NO`, NULL FROM `$table` WHERE `type`='1' LIMIT 1");
                    }
                    if ($db->fieldExists($table, 'PO_NO')) {
                        $db->query("INSERT INTO `$table` (`type`, `auto_increment`, `last_update`) SELECT 'PO_NO', `PO_NO`, NULL FROM `$table` WHERE `type`='1' LIMIT 1");
                    }
                    if ($db->fieldExists($table, 'QUO_NO')) {
                        $db->query("INSERT INTO `$table` (`type`, `auto_increment`, `last_update`) SELECT 'QUO_NO', `QUO_NO`, NULL FROM `$table` WHERE `type`='1' LIMIT 1");
                    }
                    $db->query("ALTER TABLE `$table` DROP `IN_NO`,DROP `OUT_NO`,DROP `PO_NO`,DROP `QUO_NO`");
                    $db->query("DELETE FROM `$table` WHERE `type` = '1'");
                    $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table.'` สำเร็จ</li>';
                }
                // บันทึก settings/config.php
                $config['version'] = $new_config['version'];
                if (isset($new_config['default_icon'])) {
                    $config['default_icon'] = $new_config['default_icon'];
                }
                $f = save($config, ROOT_PATH.'settings/config.php');
                $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">บันทึก <b>config.php</b> ...</li>';
                // นำเข้าภาษา
                include ROOT_PATH.'install/language.php';
            } catch (\PDOException $e) {
                $content[] = '<li class="incorrect">'.$e->getMessage().'</li>';
            } catch (\Exception $e) {
                $content[] = '<li class="incorrect">'.$e->getMessage().'</li>';
                $error = true;
            }
            if (!$error) {
                echo '<h2>ปรับรุ่นเรียบร้อย</h2>';
                echo '<p>การปรับรุ่นได้ดำเนินการเสร็จเรียบร้อยแล้ว หากคุณต้องการความช่วยเหลือในการใช้งาน คุณสามารถ ติดต่อสอบถามได้ที่ <a href="https://www.kotchasan.com" target="_blank">https://www.kotchasan.com</a></p>';
                echo '<ul>'.implode('', $content).'</ul>';
                echo '<p class=warning>กรุณาลบไดเร็คทอรี่ <em>install/</em> ออกจาก Server ของคุณ</p>';
                echo '<p>คุณควรปรับ chmod ให้ไดเร็คทอรี่ <em>datas/</em> และ <em>settings/</em> (และไดเร็คทอรี่อื่นๆที่คุณได้ปรับ chmod ไว้ก่อนการปรับรุ่น) ให้เป็น 644 ก่อนดำเนินการต่อ (ถ้าคุณได้ทำการปรับ chmod ไว้ด้วยตัวเอง)</p>';
                echo '<p><a href="../index.php" class="button large admin">เข้าระบบ</a></p>';
            } else {
                echo '<h2>ปรับรุ่นไม่สำเร็จ</h2>';
                echo '<p>การปรับรุ่นยังไม่สมบูรณ์ ลองตรวจสอบข้อผิดพลาดที่เกิดขึ้นและแก้ไขดู หากคุณต้องการความช่วยเหลือการติดตั้ง คุณสามารถ ติดต่อสอบถามได้ที่ <a href="https://www.kotchasan.com" target="_blank">https://www.kotchasan.com</a></p>';
                echo '<ul>'.implode('', $content).'</ul>';
                echo '<p><a href="." class="button large admin">ลองใหม่</a></p>';
            }
        }
    }
}

/**
 * @param Db $db
 * @param string $table_name
 * @param string $username
 * @param string $password
 * @param string $password_key
 */
function updateAdmin($db, $table_name, $username, $password, $password_key)
{
    $result = $db->first($table_name, array(
        'username' => $username,
        'status' => 1,
    ));
    if (!$result) {
        throw new \Exception('ชื่อผู้ใช้ ไม่ถูกต้อง');
    } elseif ($result->password === sha1($password.$result->salt)) {
        // password เวอร์ชั่นเก่า
        $password = sha1($password_key.$password.$result->salt);
        $db->update($table_name, array('id', $result->id), array('password' => $password));
    } elseif ($result->password != sha1($password_key.$password.$result->salt)) {
        throw new \Exception('รหัสผ่าน ไม่ถูกต้อง');
    }
}

/**
 * @param array $config
 * @param string $file
 */
function save($config, $file)
{
    $f = @fopen($file, 'wb');
    if ($f !== false) {
        if (!preg_match('/^.*\/([^\/]+)\.php?/', $file, $match)) {
            $match[1] = 'config';
        }
        fwrite($f, '<'."?php\n/* $match[1].php */\nreturn ".var_export((array) $config, true).';');
        fclose($f);
        return true;
    } else {
        return false;
    }
}
