<?php
namespace zyh\plugins\components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use zyh\plugins\components\Common;
use zyh\plugins\components\Http;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/1/24
 * Time: 上午11:13
 */
class PluginManagerBase
{
    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string
     * @throws  \Exception
     */
    protected function download($name, $extend = [])
    {
        $pluginsTmpDir = self::getTempDownloadDir();
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

        $ret = Http::sendRequest(self::getServerUrl(), array_merge(['name' => $name], $extend), 'GET', $options);
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
    protected function unzip($name)
    {
        $file = self::getTempDownloadDir() . $name . '.zip';
        $dir = Common::pluginPath() . DIRECTORY_SEPARATOR;

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
     * 备份插件
     * @param string $name 插件名称
     * @return bool
     * @throws \Exception
     */
    protected function backup($name)
    {
        $file = self::getTempDownloadDir() . $name . '-backup-' . date("YmdHis") . '.zip';
        $dir = Common::pluginPath($name) . DIRECTORY_SEPARATOR;
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            $zip->open($file, ZipArchive::CREATE);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileInfo) {
                $filePath = $fileInfo->getPathName();
                $localName = str_replace($dir, '', $filePath);
                if ($fileInfo->isFile()) {
                    $zip->addFile($filePath, $localName);
                } elseif ($fileInfo->isDir()) {
                    $zip->addEmptyDir($localName);
                }
            }
            $zip->close();
            return true;
        }
        throw new \Exception("无法执行压缩操作，请确保ZipArchive安装正确");
    }

    /**
     * 检测插件是否完整
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  \Exception
     */
    protected function check($name)
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
    protected function importSql($name)
    {
        $sqlFile = Common::pluginPath($name) . DIRECTORY_SEPARATOR . 'install.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $tempLine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $tempLine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $tempLine = str_ireplace('__PREFIX__', \Yii::$app->get('db')->tablePrefix, $tempLine);
                    $tempLine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $tempLine);
                    try {
                        \Yii::$app->get('db')->createCommand($tempLine)->execute();
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $tempLine = '';
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
        return Configs::instance()->pluginDownloadUrl;
    }

    /**
     * 临时下载目录
     * @return string
     */
    protected static function getTempDownloadDir()
    {
        return \Yii::getAlias('@runtime/plugins') . DIRECTORY_SEPARATOR;
    }
}