<?php
/* config.php */
return array(
    'version' => '3.5.0',
    'web_title' => 'OAS',
    'web_description' => 'Online Accounting System',
    'timezone' => 'Asia/Bangkok',
    'product_no' => 'P%05d',
    'QUO_NO' => 'QUO%Y%M%D-%04d',
    'OUT_NO' => 'INV%Y%M%D-%04d',
    'PO_NO' => 'PO%Y%M%D-%04d',
    'IN_NO' => 'RCV%Y%M%D-%04d',
    'currency_unit' => 'THB',
    'member_status' => array(
        0 => 'พนักงาน',
        1 => 'ผู้ดูแลระบบ',
    ),
    'default_icon' => 'icon-billing',
    'inventory_w' => 500,
);
