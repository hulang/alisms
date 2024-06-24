<?php

declare(strict_types=1);

namespace hulang\AliSms\Request;

/**
 * 请求接口类
 *
 */
interface IRequest
{
    /**
     * 返回API接口名称
     * @return mixed|string 
     */
    public function getAction();

    /**
     * 返回接口请求参数
     * @return mixed|array 
     */
    public function getParams();
}
