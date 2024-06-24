# 阿里短信接口

## 安装

```shell
composer require hulang/alisms
```

## 使用

```php
<?php
use hulang\AliSms\Client;
use hulang\AliSms\Request\SendSms;

$config = [
    'accessKeyId'    => 'LTAIbVA2LRQ1tULr',
    'accessKeySecret' => 'ocS48RUuyBPpQHsfoWokCuz8ZQbGxl',
];

$client  = new Client($config);
$sendSms = new SendSms;
$sendSms->setPhoneNumbers('1500000000');
$sendSms->setSignName('天天');
$sendSms->setTemplateCode('SMS_898785');
$sendSms->setTemplateParam(['code' => rand(111111, 999999)]);
$sendSms->setOutId('demo');

print_r($client->execute($sendSms));
```
