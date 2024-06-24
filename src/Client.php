<?php

declare(strict_types=1);

namespace hulang\AliSms;

use hulang\AliSms\Request\IRequest;

/**
 * dysms客户端类
 *
 */
class Client
{
    /**
     * 接口地址
     * @var mixed|string
     */
    protected $api_uri = 'http://dysmsapi.aliyuncs.com/';

    /**
     * 回传格式
     * @var mixed|string
     */
    protected $format = 'json';

    /**
     * 签名方式
     * @var mixed|string
     */
    protected $signatureMethod = 'HMAC-SHA1';

    /**
     * 接口请求方式[GET/POST]
     * @var mixed|string
     */
    protected $httpMethod = 'POST';

    /**
     * 配置项
     * @var mixed|array
     */
    protected $config = [];

    /**
     * 构造函数用于实例化对象并初始化配置数组
     * 
     * @param mixed|array $config 初始化对象时的配置数组,可以为空,默认为空数组
     *                      配置数组用于设置对象的各种初始状态或选项
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 执行请求操作
     * 
     * 本函数负责根据传入的请求对象,构造请求参数,发送请求,并处理响应
     * 它主要包括以下步骤:
     * 1. 从请求对象中获取操作名称和请求参数
     * 2. 合并公共参数、操作名称和请求参数
     * 3. 为参数生成签名,并添加到参数数组中
     * 4. 使用cURL发送带有签名的参数请求到API接口
     * 5. 解析JSON格式的响应,并转换键的大小写
     * 6. 返回处理后的响应结果
     * 
     * @param IRequest $request 请求对象,包含操作名称和请求参数
     * @return mixed|array 处理后的响应结果,键的大小写根据实际情况可能有所变化
     */
    public function execute(IRequest $request)
    {
        $action = $request->getAction();
        $reqParams = $request->getParams();
        $pubParams = $this->getPublicParams();
        $params = array_merge(
            $pubParams,
            ['Action' => $action],
            $reqParams
        );
        // 签名
        $params['Signature'] = $this->generateSign($params);
        // 请求数据
        $resp = $this->curl(
            $this->api_uri,
            $params
        );
        $arr = json_decode($resp, true);
        return $arr;
    }

    /**
     * 生成请求签名
     * 本函数用于根据传入的参数数组生成请求的签名,签名算法采用HMAC-SHA1
     * 签名生成过程:
     * 1. 对参数数组按照键名升序排序
     * 2. 将排序后的参数键值对进行URL编码,并使用等号连接
     * 3. 将等号连接得到的参数串按照键名升序拼接成查询字符串
     * 4. 构造待签名的字符串,格式为:HTTPMethod + '&' + percentEncode('/') + '&' + percentEncode(查询字符串)
     * 5. 使用accessKeySecret及SHA1算法计算待签名字符串的HMAC值
     * 6. 将HMAC值进行Base64编码,得到签名值
     *
     * @param array $params 待签参数数组
     * @return mixed|string 生成的签名值
     */
    protected function generateSign($params = [])
    {
        ksort($params);
        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = $this->percentEncode($k) . '=' . $this->percentEncode($v);
        }
        $queryStr = implode('&', $arr);
        $strToSign = $this->httpMethod . '&%2F&' . $this->percentEncode($queryStr);
        return base64_encode(hash_hmac('sha1', $strToSign, $this->config['accessKeySecret'] . '&', true));
    }

    /**
     * 对字符串进行URL编码
     * 
     * 该方法对字符串进行特定的URL编码,以符合RFC3986的规范
     * 它首先使用urlencode对字符串进行编码,然后对特定的字符进行二次处理,
     * 包括将加号(+)转为百分号加20(%20),将星号(*)转为百分号加2A(%2A),
     * 将波浪线(~)转为百分号加7E(%7E)
     * 这样的编码方式用于构建RFC3986兼容的查询字符串
     * 
     * @param string $str 待编码的字符串
     * @return mixed|string 编码后的字符串
     */
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * 构建请求所需的公共参数
     * 这些参数是每次请求都需要包含的公共字段,用于身份验证和请求时间戳等
     * 
     * @return mixed|array 包含公共参数的数组
     */
    protected function getPublicParams()
    {
        return [
            'AccessKeyId' => $this->config['accessKeyId'],
            'Timestamp' => $this->getTimestamp(),
            'Format' => $this->format,
            'SignatureMethod' => $this->signatureMethod,
            'SignatureVersion' => '1.0',
            'SignatureNonce' => uniqid(),
            'Version' => '2017-05-25',
            'RegionId' => 'cn-hangzhou',
        ];
    }

    /**
     * 获取标准GMT时间戳
     * 
     * 本函数用于生成符合ISO 8601标准的GMT时间戳,格式为YYYY-MM-DDTHH:MM:SSZ
     * 其中,T为时间分隔符,Z表示时区为零时区(GMT)
     * 
     * @return mixed|string 返回格式化的GMT时间戳字符串
     */
    protected function getTimestamp()
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('GMT');
        $timestamp = date('Y-m-d\TH:i:s\Z');
        date_default_timezone_set($timezone);
        return $timestamp;
    }

    /**
     * 使用cURL库发送HTTP请求
     * 
     * 该函数初始化一个cURL会话,配置请求选项,并发送请求
     * 它支持HTTP POST方法,并自动处理HTTPS连接的SSL验证
     * 
     * @param string $url 请求的URL地址
     * @param array|null $postFields HTTP POST请求的数据数组
     * @return mixed|array|string 返回从服务器接收到的响应
     */
    protected function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // https请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_array($postFields) && 0 < count($postFields)) {
            $postBodyString = '';
            foreach ($postFields as $k => $v) {
                $postBodyString .= '$k=' . urlencode($v) . '&';
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            $header = array('content-type: application/x-www-form-urlencoded; charset=UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
        }
        $reponse = curl_exec($ch);
        return $reponse;
    }
}
