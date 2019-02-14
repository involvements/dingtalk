<?php

/*
 * This file is part of the mingyoung/dingtalk.
 *
 * (c) 张铭阳 <mingyoungcheung@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyDingTalk\Kernel;

use Overtrue\Http\Traits\ResponseCastable;
use function EasyDingTalk\helpers;

class AccessToken
{
    use Concerns\InteractsWithCache, ResponseCastable;

    /**
     * @var \EasyDingTalk\Application
     */
    protected $app;

    /**
     * AccessToken constructor.
     *
     * @param \EasyDingTalk\Application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 获取钉钉 AccessToken
     *
     * @return array
     */
    public function get()
    {
        if ($value = $this->getCache()->get($this->cacheFor())) {
            return $value;
        }

        return $this->refresh();
    }

    /**
     * 获取 AccessToken
     *
     * @return string
     */
    public function getToken()
    {
        return $this->get()['access_token'];
    }

    /**
     * 刷新钉钉 AccessToken
     *
     * @return array
     */
    public function refresh()
    {
        $response = $this->app['client']->requestRaw('gettoken', 'GET', ['query' => [
            'appkey' => $this->app['config']->get('app_key'),
            'appsecret' => $this->app['config']->get('app_secret'),
        ]]);

        return tap($this->castResponseToType($response, 'array'), function ($value) {
            $this->getCache()->set($this->cacheFor(), $value, $value['expires_in']);
        });
    }

    /**
     * 缓存 Key
     *
     * @return string
     */
    protected function cacheFor()
    {
        return sprintf('access_token.%s', $this->app['config']->get('app_key'));
    }
}
