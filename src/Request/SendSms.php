<?php

declare(strict_types=1);

namespace hulang\AliSms\Request;

/**
 * 短信发送API
 *
 */
class SendSms extends ARequest implements IRequest
{
    /**
     * API名称
     * @var string
     */
    protected $action = 'SendSms';

    /**
     * 设置短信发送的手机号码
     * 
     * 本函数用于设定将要发送短信的手机号码
     * 支持单个手机号码或者以数组形式传入多个手机号码,多个手机号码之间使用逗号分隔
     * 注意,批量发送短信可能会有稍许延迟,对于要求及时性的短信,建议单个手机号码调用
     * 
     * @param string|array $value 单个手机号码或者包含多个手机号码的数组
     * @return object 返回当前对象,支持链式调用
     */
    public function setPhoneNumbers($value = '')
    {
        // 判断传入的手机号码是否为数组,如果是,将其合并为字符串,各手机号码间用逗号分隔
        $this->params['PhoneNumbers'] = is_array($value) ? implode(',', $value) : $value;
        // 返回当前对象,支持链式调用
        return $this;
    }

    /**
     * 设置短信签名
     * 
     * 本函数用于设置短信发送时的签名信息
     * 签名是用于识别短信发送方的一种方式,它可以是企业名称、应用名称或其他具有辨识度的标识
     * 设置签名后,所有通过本类发送的短信将会携带该签名
     * 
     * @param string $value 短信签名的内容.必须是已经阿里云短信平台审核通过的签名
     * @return $this 返回当前对象实例,支持链式调用
     */
    public function setSignName($value)
    {
        // 将设置的签名保存到参数数组中
        $this->params['SignName'] = $value;
        // 返回当前对象实例,支持链式调用
        return $this;
    }

    /**
     * 设置短信模板ID
     * 
     * 本函数用于设置发送短信时所使用的模板ID
     * 模板ID是预先在短信服务提供商平台注册并审核通过的,它用于确定短信的具体内容和格式
     * 通过调用本函数,可以将指定的模板ID赋值给内部参数,以便在后续的短信发送操作中使用
     * 
     * @param string $value 短信模板的唯一标识ID
     * 
     * @return $this 返回当前对象,支持链式调用
     */
    public function setTemplateCode($value)
    {
        // 将传入的模板ID赋值给params数组中的TemplateCode键
        $this->params['TemplateCode'] = $value;
        // 返回当前对象,支持链式调用
        return $this;
    }

    /**
     * 设置短信模板参数
     * 
     * 本方法用于设置短信模板的变量参数
     * 这些参数将以JSON格式存储,以便于后续的短信发送过程中,将这些参数动态地应用到短信模板中,从而生成个性化的短信内容
     * 
     * @param array $value 短信模板的变量参数,以键值对数组的形式提供
     *                    键代表参数名称,值代表参数的具体内容
     * 
     * @return $this 返回当前对象实例,支持链式调用
     */
    public function setTemplateParam($value = [])
    {
        // 将变量参数数组转换为JSON格式的字符串,并强制转换为对象格式,以兼容不支持数组的场景
        $this->params['TemplateParam'] = json_encode($value, JSON_FORCE_OBJECT);
        // 返回当前对象实例,支持链式调用
        return $this;
    }

    /**
     * 设置外部流水扩展字段
     * 
     * 该方法用于为支付请求设置一个外部流水号
     * 这个外部流水号是由商家自己生成的,它的作用是作为商家系统内部订单支付请求的唯一标识
     * 在支付过程中,商家可以通过这个外部流水号来查询订单支付状态,或者与自己的订单系统进行关联
     * 
     * @param string $value 外部流水扩展字段的值.这个值应该由商家自己生成,确保在商家系统内唯一
     * @return $this 返回当前对象,支持链式调用
     */
    public function setOutId($value)
    {
        $this->params['OutId'] = $value;
        return $this;
    }
}
