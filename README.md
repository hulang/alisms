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
}
```
