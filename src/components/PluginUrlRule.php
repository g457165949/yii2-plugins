<?php

namespace zyh\plugins\components;


use yii\base\BaseObject;
use yii\web\UrlRuleInterface;

class PluginUrlRule extends BaseObject implements UrlRuleInterface
{


    public function parseRequest($manager, $request)
    {

        if (!$request->pathInfo) {
            return false;
        }

        $pathArr = explode('/', $request->pathInfo);

        // 判断是否是插件市场
        if($pathArr[0] == 'plugins'){
            // 插件广场路由规则
            if (in_array($request->pathInfo, ['plugins/index', 'plugins'])) {
                return ['plugins/plugins/index', []];
            }else{
                return ['plugins/'.$request->pathInfo,[]];
            }
        }

        // 插件路由规则
        $plugins = Common::getCache($pathArr[0], 'plugins', []);
        if ($plugins) {
            $num = count($pathArr);
            $url = $num == 1 ? '' : $request->pathInfo;

            switch ($num) {
                case 1:
                    $url = $pathArr[0] . '/index';
                case 2:
                    $url = $pathArr[0] . '/' . $url;
            }
            return ['plugins/' . $url, []];
        }

        return false; // this rule does not apply
    }

    public function createUrl($manager, $route, $params)
    {

        return false;
    }
}