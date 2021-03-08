<?php

if (defined('ROOT_PATH')) {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin@localhost';
    $password = isset($_SESSION['password']) ? $_SESSION['password'] : 'admin';
    echo '<form method=post action=index.php autocomplete=off>';
    echo '<h2>สมาชิกผู้ดูแลระบบ</h2>';
    echo '<p>คุณจะต้องระบุข้อมูลสมาชิกผู้ดูแลระบบ ซึ่งจะมีสิทธิสูงสุดในระบบ</p>';
    echo '<p class=item><label for=username>ชื่อผู้ใช้</label><span class="g-input icon-user"><input type=text size=50 id=username name=username value="'.$username.'"></span></p>';
    if ($username == '') {
        echo '<p class=comment><em>กรุณากรอก ชื่อผู้ใช้ของคุณ</em></p>';
    } else {
        echo '<p class=comment>ชื่อผู้ใช้ของคุณ</p>';
    }
    echo '<p class=item><label for=password>รหัสผ่าน</label><span class="g-input icon-password"><input type=password size=50 id=password name=password value="'.$password.'"></span></p>';
    if ($password == '') {
        echo '<p class=comment><em>กรุณากรอก รหัสผ่านที่ใช้ในการเข้าระบบของคุณ</em></p>';
    } else {
        echo '<p class=comment>รหัสผ่านที่ใช้ในการเข้าระบบของคุณ</p>';
    }
    echo '<input type=hidden name=step value=3>';
    echo '<p><input class="button large save" type=submit value="ดำเนินการต่อ"></p>';
    echo '</form>';
}
