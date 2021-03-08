<?php
/**
 * @filesource Kotchasan/ApiController.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Kotchasan;

use Kotchasan\Http\Request;

/**
 * API Controller base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ApiController extends KBase
{
    /**
     * แม่แบบคอนโทรลเลอร์ สำหรับ API
     *
     * @param Request $request
     *
     * @return JSON
     */
    public function index(Request $request)
    {
        if (empty(self::$cfg->api_token) || empty(self::$cfg->api_ips)) {
            // ยังไม่ได้สร้าง Token หรือ ยังไม่ได้อนุญาต IP
            $result = array(
                'code' => 503,
                'message' => 'Unavailable API',
            );
        } elseif (in_array('0.0.0.0', self::$cfg->api_ips) || in_array($request->getClientIp(), self::$cfg->api_ips)) {
            try {
                // รับค่าที่ส่งมาจาก Router
                $module = $request->get('module')->filter('a-z0-9');
                $method = $request->get('method')->filter('a-z');
                $action = $request->get('action')->filter('a-z');
                // แปลงเป็นชื่อคลาส สำหรับ Model เช่น
                // api.php/v1/user/create ได้เป็น V1\User\Model::create
                $className = ucfirst($module).'\\'.ucfirst($method).'\\Model';
                // ตรวจสอบ method
                if (method_exists($className, $action)) {
                    // เรียกใช้งาน Class
                    $result = createClass($className)->$action($request);
                } else {
                    // error ไม่พบ class หรือ method
                    $result = array(
                        'code' => 404,
                        'message' => 'Object Not Found',
                    );
                }
            } catch (ApiException $e) {
                // API Error
                $result = array(
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                );
            }
        } else {
            // ไม่อนุญาต IP
            $result = array(
                'code' => 403,
                'message' => 'Forbidden',
            );
        }
        // Response คืนค่ากลับเป็น JSON ตาม $result
        $response = new \Kotchasan\Http\Response();
        $response->withHeaders(array(
            'Content-type' => 'application/json; charset=UTF-8',
        ))
            ->withStatus(empty($result['code']) ? 200 : $result['code'])
            ->withContent(json_encode($result))
            ->send();
    }

    /**
     * ตรวจสอบ Token
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จคืนค่าข้อผิดพลาด ApiException Invalid token
     *
     * @param string $token
     *
     * @return bool
     */
    public static function validateToken($token)
    {
        if (self::$cfg->api_token === $token) {
            return true;
        }
        throw new ApiException('Invalid token', 401);
    }

    /**
     * ตรวจสอบ Token Bearer
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จคืนค่าข้อผิดพลาด ApiException Invalid token
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function validateTokenBearer(Request $request)
    {
        if (preg_match('/^Bearer\s'.self::$cfg->api_token.'$/', $request->getHeaderLine('Authorization'))) {
            return true;
        }
        throw new ApiException('Invalid token', 401);
    }

    /**
     * ตรวจสอบ sign
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จคืนค่าข้อผิดพลาด ApiException Invalid sign
     *
     * @param $params
     *
     * @return bool
     */
    public static function validateSign($params)
    {
        if (count($params) > 1 && isset($params['sign'])) {
            $sign = $params['sign'];
            unset($params['sign']);
            if ($sign === \Kotchasan\Password::generateSign($params, self::$cfg->api_secret)) {
                return true;
            }
        }
        throw new ApiException('Invalid sign', 403);

    }

    /**
     * ตรวจสอบ Method
     * สำเร็จ คืนค่า true
     * ไม่สำเร็จคืนค่าข้อผิดพลาด ApiException Method not allowed
     *
     * @param Request $request
     * @param string $method Method เช่น POST GET PUT DELETE OPTIONS
     *
     * @return bool
     */
    public static function validateMethod(Request $request, $method)
    {
        if ($request->getMethod() === $method) {
            return true;
        }
        throw new ApiException('Method not allowed', 405);
    }
}
