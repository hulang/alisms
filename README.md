# 阿里短信接口

### 环境

- php >=7.4.0

## 安装

```shell
composer require hulang/alisms
```

## 使用

```php
if (!function_exists('CreateSmsClient')) {
    /**
     * 创建短信客户端,用于发送短信
     * 
     * 通过传入的访问密钥ID和密钥Secret,以及短信发送的手机号码、模板代码、模板参数和签名
     * 创建并配置一个短信客户端,执行发送短信的操作
     * 返回发送结果的数组,包含状态码、消息和数据
     * 
     * @param string $keyId 访问密钥ID
     * @param string $keySecret 访问密钥Secret
     * @param string $phone 短信发送的手机号码
     * @param string $templateCode 短信模板的代码
     * @param array $templateParam 短信模板的参数
     * @param string $signName 短信签名
     * @return array 发送短信的结果,包含状态码、消息和数据
     */
    function CreateSmsClient($keyId = '', $keySecret = '', $phone = '', $templateCode = '', $templateParam = [], $signName = '')
    {
        // 初始化返回结果数组
        $result = [];
        $result['code'] = 0;
        $result['msg'] = '';
        $result['data'] = [];
        try {
            // 配置阿里云短信服务的访问密钥
            $config = [
                'accessKeyId' => $keyId,
                'accessKeySecret' => $keySecret,
            ];
            // 创建短信客户端
            $client = new Client($config);
            // 创建发送短信的请求对象
            $sendSms = new SendSms;
            // 设置短信发送的手机号码
            $sendSms->setPhoneNumbers($phone);
            // 设置短信签名
            $sendSms->setSignName($signName);
            // 设置短信模板代码
            $sendSms->setTemplateCode($templateCode);
            // 如果模板参数不为空,则设置模板参数
            if (!empty($templateParam)) {
                $sendSms->setTemplateParam($templateParam);
            }
            // 执行发送短信的操作,获取发送结果
            $arr = $client->execute($sendSms);
            // 如果发送成功,更新返回结果数组
            if ($arr['code'] == 'ok') {
                $result['code'] = 1;
                $result['msg'] = '';
            }
            $result['data'] = $arr;
        } catch (\Exception $e) {
            // 如果发送过程中出现异常,记录异常消息到返回结果中
            $result['msg'] = $e->getMessage();
        }
        // 返回发送短信的结果
        return $result;
    }
}
```
