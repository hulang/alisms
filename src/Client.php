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
        // 获取请求的操作名称
        $action = $request->getAction();
        // 获取请求的特定参数
        $reqParams = $request->getParams();
        // 获取通用的公共参数
        $pubParams = $this->getPublicParams();
        // 合并公共参数、操作名称和请求参数,为发送请求做准备
        $params = array_merge($pubParams, ['Action' => $action], $reqParams);
        // 生成请求的签名,并添加到参数数组中
        $params['Signature'] = $this->generateSign($params);
        // 使用cURL发送请求,并获取响应
        $resp = $this->curl($this->api_uri, $params);
        // 解析JSON格式的响应,并转换为关联数组
        $arr = json_decode($resp, true);
        // 将响应数组中的键的大小写转换为统一格式,并返回
        $result = array_map('array_change_key_case', $arr);
        return $result;
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
        // 对参数数组按照键名升序排序
        ksort($params);
        // 构建编码后的参数字符串数组
        $arr = [];
        foreach ($params as $k => $v) {
            $arr[] = $this->percentEncode($k) . '=' . $this->percentEncode($v);
        }
        // 将编码后的参数字符串数组拼接成查询字符串
        $queryStr = implode('&', $arr);
        // 构造待签名的字符串
        $strToSign = $this->httpMethod . '&%2F&' . $this->percentEncode($queryStr);
        // 计算签名并返回
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
        // 使用urlencode对字符串进行基本编码
        $res = urlencode($str);
        // 将加号(+)转为百分号加20(%20)
        $res = preg_replace('/\+/', '%20', $res);
        // 将星号(*)转为百分号加2A(%2A)
        $res = preg_replace('/\*/', '%2A', $res);
        // 将波浪线(~)转为百分号加7E(%7E)
        $res = preg_replace('/%7E/', '~', $res);
        // 返回处理后的编码字符串
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
        // 返回一个数组,包含所有公共请求参数
        return [
            // AccessKeyId 是访问密钥ID,用于身份验证
            'AccessKeyId' => $this->config['accessKeyId'],
            // 时间戳,用于防止重放攻击
            'Timestamp' => $this->getTimestamp(),
            // 返回结果的格式,如 JSON 或 XML
            'Format' => $this->format,
            // 签名方法,目前只支持 HMAC-SHA1
            'SignatureMethod' => $this->signatureMethod,
            // 签名版本,目前版本是1.0
            'SignatureVersion' => '1.0',
            // 签名唯一随机数,用于防止网络重放攻击
            'SignatureNonce' => uniqid(),
            // API的版本号
            'Version' => '2017-05-25',
            // 服务的区域ID,指定服务所在的地域
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
        // 获取当前默认时区,以便在设置GMT时区后恢复
        $timezone = date_default_timezone_get();
        // 将默认时区设置为GMT,以获取GMT时间
        date_default_timezone_set('GMT');
        // 生成符合ISO 8601标准的GMT时间戳
        $timestamp = date('Y-m-d\TH:i:s\Z');
        // 恢复先前的默认时区设置
        date_default_timezone_set($timezone);
        // 返回GMT时间戳字符串
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
        // 初始化cURL会话
        $ch = curl_init();
        // 设置cURL选项:请求URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置cURL不立即失败,而是返回错误代码
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        // 设置cURL选项:返回响应而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 如果URL是HTTPS,禁用SSL验证以简化测试
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        // 如果提供了POST字段,准备POST数据
        if (is_array($postFields) && 0 < count($postFields)) {
            // 构建POST数据字符串
            $postBodyString = '';
            foreach ($postFields as $k => $v) {
                $postBodyString .= '$k=' . urlencode($v) . '&';
            }
            unset($k, $v);
            // 设置请求为POST方式
            curl_setopt($ch, CURLOPT_POST, true);
            // 设置HTTP头部信息,指定POST数据的Content-Type
            $header = array('content-type: application/x-www-form-urlencoded; charset=UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            // 设置POST数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
        }
        // 执行cURL请求并获取响应
        $reponse = curl_exec($ch);
        // 关闭cURL会话并返回响应
        return $reponse;
    }
}
