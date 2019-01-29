<?php
namespace zyh\plugins\services;

use ZipArchive;
use zyh\plugins\components\Common;
use zyh\plugins\components\Http;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/24
 * Time: 上午11:13
 */
class Service
{
    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string
     * @throws  \Exception
     */
    public static function download($name, $extend = [])
    {
        $pluginsTmpDir = \Yii::getAlias('@runtime') . '/plugins' . DIRECTORY_SEPARATOR;
        if (!is_dir($pluginsTmpDir)) {
            @mkdir($pluginsTmpDir, 0755, true);
        }
        $tmpFile = $pluginsTmpDir . $name . ".zip";
        $options = [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];

        $ret = Http::sendRequest(self::getServerUrl() . '/plugins/download', array_merge(['name' => $name], $extend), 'GET', $options);
        if ($ret['ret']) {
            if (substr($ret['msg'], 0, 1) == '{') {
                $json = (array)json_decode($ret['msg'], true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    array_pop($options);
                    $ret = Http::sendRequest($json['data']['url'], [], 'GET', $options);
                    if (!$ret['ret']) {
                        //下载返回错误，抛出异常
                        return [
                            'code' => $json['code'],
                            'msg' => $json['msg'],
                            'data' => $json['data'],
                        ];
                    }
                } else {
                    //下载返回错误，抛出异常
                    return [
                        'code' => $json['code'],
                        'msg' => $json['msg'],
                        'data' => $json['data'],
                    ];
                }
            }
            if ($write = fopen($tmpFile, 'w')) {
                fwrite($write, $ret['msg']);
                fclose($write);
                return $tmpFile;
            }
            throw new \Exception("没有权限写入临时文件");
        }
        throw new \Exception("无法下载远程文件");
    }

    /**
     * 解压插件
     *
     * @param   string $name 插件名称
     * @return  string
     * @throws  \Exception
     */
    public static function unzip($name)
    {
        $file = \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $name . '.zip';
        $dir = Common::pluginPath()  . DIRECTORY_SEPARATOR;

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($file) !== TRUE) {
                throw new \Exception('Unable to open the zip file');
            }
            if (!$zip->extractTo($dir)) {
                $zip->close();
                throw new \Exception('Unable to extract the file');
            }
            $zip->close();
            return $dir;
        }
        throw new \Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }

    /**
     * 检测插件是否完整
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  \Exception
     */
    public static function check($name)
    {
        if (!$name || !is_dir(Common::pluginPath($name))) {
            throw new \Exception('Plugin not exists');
        }
        $pluginClass = Common::getPluginClass($name);
        if (!$pluginClass) {
            throw new \Exception("插件主启动程序不存在");
        }

        $plugin = new $pluginClass();
        if (!$plugin->checkInfo()) {
            throw new \Exception("配置文件不完整");
        }
        return true;
    }

    /**
     * 导入SQL
     *
     * @param   string $name 插件名称
     * @return  boolean
     */
    public static function importSql($name)
    {
        $sqlFile = Common::pluginPath($name) . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', \Yii::$app->get('db')->tablePrefix.'_', $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        \Yii::$app->get('db')->createCommand($templine)->execute();
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }


    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return \Yii::$app->getModule('plugins')->pluginDownloadUrl;
    }
}