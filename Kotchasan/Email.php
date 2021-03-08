<?php
/**
 * @filesource Kotchasan/Email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Email function.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Email extends \Kotchasan\KBase
{
    /**
     * @var mixed
     */
    protected $error;

    /**
     * ตรวจสอบว่ามีข้อผิดพลาดในการส่งอีเมลหรือไม่
     * คืนค่า true ถ้ามี error, false ถ้าส่งอีเมลสำเร็จ.
     *
     * @return bool
     */
    public function error()
    {
        return empty($this->error) ? false : true;
    }

    /**
     * คืนค่าข้อผิดพลาดการส่งอีเมล
     * ถ้าไม่มีข้อผิดพลาดคืนค่าข้อความว่าง
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return empty($this->error) ? '' : implode("\n", $this->error);
    }

    /**
     * ฟังก์ชั่นส่งเมล์แบบกำหนดรายละเอียดเอง
     *
     * @param string $mailto  ที่อยู่อีเมลผู้รับ User Name<email@domain.tld> หรือ email@domain.tld คั่นแต่ละรายการด้วย ,
     * @param string $replyto ที่อยู่อีเมลสำหรับการตอบกลับจดหมาย ถ้าระบุเป็นค่าว่างจะใช้ที่อยู่อีเมลจาก noreply_email
     * @param string $subject หัวข้อจดหมาย
     * @param string $msg รายละเอียดของจดหมาย (รองรับ HTML)
     * @param string $cc ที่อยู่อีเมล์สำหรับ ส่งแบบ CC คั่นแต่ละรายการด้วย ,
     * @param string $bcc ที่อยู่อีเมล์สำหรับ ส่งแบบ BCC คั่นแต่ละรายการด้วย ,
     *
     * @return \static
     */
    public static function send($mailto, $replyto, $subject, $msg, $cc = '', $bcc = '')
    {
        // create class
        $obj = new static();
        $obj->error = array();
        // send email
        $charset = empty(self::$cfg->email_charset) ? 'utf-8' : strtolower(self::$cfg->email_charset);
        if (empty($replyto)) {
            $replyto = array(strip_tags(self::$cfg->web_title), self::$cfg->noreply_email);
        } elseif (preg_match('/^(.*)<(.*?)>$/', $replyto, $match)) {
            $replyto = array(strip_tags($match[1]), (empty($match[2]) ? $match[1] : $match[2]));
        } else {
            $replyto = array($replyto, $replyto);
        }
        if ($charset != 'utf-8') {
            $subject = iconv('utf-8', $charset, $subject);
            $msg = iconv('utf-8', $charset, $msg);
            $replyto[0] = iconv('utf-8', $charset, $replyto[0]);
        }
        $msg = preg_replace(array('/<\?/', '/\?>/'), array('&lt;?', '?&gt;'), $msg);
        if (empty(self::$cfg->email_use_phpMailer)) {
            // ส่งอีเมลด้วยฟังก์ชั่นของ PHP
            $emails = array($mailto);
            if ($cc != '') {
                $emails[] = $cc;
            }
            if ($bcc != '') {
                $emails[] = $bcc;
            }
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= 'Content-type: text/html; charset='.strtoupper($charset)."\r\n";
            $headers .= 'From: '.$replyto[0]."\r\n";
            $headers .= "Reply-to: $replyto[1]\r\n";
            if (!@mail(implode(',', $emails), $subject, $msg, $headers)) {
                $obj->error['Unable to send mail'] = Language::get('Unable to send mail');
            }
        } else {
            // ส่งอีเมลด้วย PHPMailer
            include_once VENDOR_DIR.'PHPMailer/class.phpmailer.php';
            // Create a new PHPMailer instance
            $mail = new \PHPMailer();
            if (self::$cfg->email_use_phpMailer == 1) {
                // Send messages using SMTP
                $mail->isSMTP();
            } else {
                // Send messages using PHP's mail() function
                $mail->isMail();
            }
            // charset
            $mail->CharSet = $charset;
            // use html
            $mail->IsHTML();
            $mail->SMTPAuth = empty(self::$cfg->email_SMTPAuth) ? false : true;
            if ($mail->SMTPAuth) {
                $mail->Username = self::$cfg->email_Username;
                $mail->Password = self::$cfg->email_Password;
                $mail->SMTPSecure = self::$cfg->email_SMTPSecure;
            }
            if (!empty(self::$cfg->email_Host)) {
                $mail->Host = self::$cfg->email_Host;
            }
            if (!empty(self::$cfg->email_Port)) {
                $mail->Port = self::$cfg->email_Port;
            }
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            );
            $mail->AddReplyTo($replyto[1], $replyto[0]);
            if ($mail->ValidateAddress(self::$cfg->noreply_email)) {
                $mail->SetFrom(self::$cfg->noreply_email, strip_tags(self::$cfg->web_title));
            }
            // subject
            $mail->Subject = $subject;
            // message
            $mail->MsgHTML(preg_replace('/(<br([\s\/]{0,})>)/', "$1\r\n", $msg));
            $mail->AltBody = strip_tags($msg);
            foreach (explode(',', $mailto) as $email) {
                if (preg_match('/^(.*)<(.*)>$/', $email, $match)) {
                    if ($mail->validateAddress($match[2])) {
                        $mail->addAddress($match[2], strip_tags($match[1]));
                    }
                } elseif ($mail->validateAddress($email)) {
                    $mail->addAddress($email);
                }
                if ($cc != '') {
                    foreach (explode(',', $cc) as $cc_email) {
                        if ($mail->validateAddress($cc_email)) {
                            $mail->addCC($cc_email);
                        }
                    }
                }
                if ($bcc != '') {
                    foreach (explode(',', $bcc) as $bcc_email) {
                        if ($mail->validateAddress($bcc_email)) {
                            $mail->addBCC($bcc_email);
                        }
                    }
                }
                $err = $mail->send();
                if ($err === false) {
                    $obj->error[$mail->ErrorInfo] = strip_tags($mail->ErrorInfo);
                }
                $mail->clearAddresses();
            }
        }
        return $obj;
    }
}
