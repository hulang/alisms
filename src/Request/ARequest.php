<?php

declare(strict_types=1);

namespace hulang\AliSms\Request;

/**
 * 抽象类
 *
 */
abstract class ARequest implements IRequest
{
    /**
     * API接口名称
     * @var mixed|string
     */
    protected $action;

    /**
     * 接口请求参数
     * @var mixed|array
     */
    protected $params = [];

    /**
     * 返回API名称
     * @return mixed|string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 接口请求参数
     * @return mixed|array 
     */
    public function getParams()
    {
        return $this->params;
    }
}
