<?php
/**
+------------------------------------------------------------------------------
* 获取服务器信息类

+------------------------------------------------------------------------------
*/
class ServerInfo
{
//类定义开始

    /**
     +----------------------------------------------------------
     * 获取服务器时间
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetServerTime()
    {
        return date('Y-m-d　H:i:s');
    }

    /**
     +----------------------------------------------------------
     * 获取服务器解译引擎
     * 例如：Apache/2.2.8 (Win32) PHP/5.2.6
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetServerSoftwares()
    {
        return $_SERVER['SERVER_SOFTWARE'];
    }

    /**
     +----------------------------------------------------------
     * 获取php版本号
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetPhpVersion()
    {
        return PHP_VERSION;
    }

    /**
     +----------------------------------------------------------
     * 获取Mysql版本号
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetMysqlVersion()
    {
        $con = mysql_connect("localhost", "root", "root");
        return mysql_get_server_info($con);
    }

    /**
     +----------------------------------------------------------
     * 获取Http版本号
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetHttpVersion()
    {
        return $_SERVER['SERVER_PROTOCOL'];
    }

    /**
     +----------------------------------------------------------
     * 获取网站根目录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetDocumentRoot()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    /**
     +----------------------------------------------------------
     * 获取PHP脚本最大执行时间
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetMaxExecutionTime()
    {
        return ini_get('max_execution_time').' Seconds';
    }

    /**
     +----------------------------------------------------------
     * 获取服务器允许文件上传的大小
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetServerFileUpload()
    {
        if (@ini_get('file_uploads')) {
            return '允许 '.ini_get('upload_max_filesize');
        } else {
            return '<font color="red">禁止</font>';
        }
    }

    /**
     +----------------------------------------------------------
     * 获取全局变量 register_globals的设置信息 On/Off
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetRegisterGlobals()
    {
        return $this->GetPhpCfg('register_globals');
    }

    /**
     +----------------------------------------------------------
     * 获取安全模式 safe_mode的设置信息 On/Off
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetSafeMode()
    {
        return $this->GetPhpCfg('safe_mode');
    }

    /**
     +----------------------------------------------------------
     * 获取Gd库的版本号
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetGdVersion()
    {
        if(function_exists('gd_info')){
            $GDArray = gd_info();
            $gd_version_number = $GDArray['GD Version'] ? '版本：'.$GDArray['GD Version'] : '不支持';
        }else{
            $gd_version_number = '不支持';
        }
        return $gd_version_number;
    }

    /**
     +----------------------------------------------------------
     * 获取内存占用率
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function GetMemoryUsage()
    {
        return $this->ConversionDataUnit(memory_get_usage());
    }

    /**
     +----------------------------------------------------------
     * 对数据单位 (字节)进行换算
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function ConversionDataUnit($size)
    {
        $kb = 1024;       // Kilobyte
        $mb = 1024 * $kb; // Megabyte
        $gb = 1024 * $mb; // Gigabyte
        $tb = 1024 * $gb; // Terabyte
        //round() 对浮点数进行四舍五入
        if($size < $kb) {
            return $size.' Byte';
        }
        else if($size < $mb) {
            return round($size/$kb,2).' KB';
        }
        else if($size < $gb) {
            return round($size/$mb,2).' MB';
        }
        else if($size < $tb) {
            return round($size/$gb,2).' GB';
        }
        else {
            return round($size/$tb,2).' TB';
        }
    }

    /**
     +----------------------------------------------------------
     * 获取PHP配置文件 (php.ini)的值
     +----------------------------------------------------------
     * @param string $val 值
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function GetPhpCfg($val)
    {
        switch($result = get_cfg_var($val)) {
        case 0:
            return '关闭';
            break;
        case 1:
            return '打开';
            break;
        default:
            return $result;
            break;
        }
    }

}//类定义结束
date_default_timezone_set('PRC');
$ServerInfo = new ServerInfo();
$rs .= '服务器时间：'.$ServerInfo->GetServerTime().'<br>';
$rs .= '服务器解译引擎：'.$ServerInfo->GetServerSoftwares().'<br>';
$rs .= 'PHP版本：'.$ServerInfo->GetPhpVersion().'<br>';
$rs .= 'MYSQL版本：'.$ServerInfo->GetMysqlVersion().'<br>';
$rs .= 'HTTP版本：'.$ServerInfo->GetHttpVersion().'<br>';
$rs .= '网站根目录：'.$ServerInfo->GetDocumentRoot().'<br>';
$rs .= '最大执行时间：'.$ServerInfo->GetMaxExecutionTime().'<br>';
$rs .= '文件上传：'.$ServerInfo->GetServerFileUpload().'<br>';
$rs .= '全局变量 register_globals：'.$ServerInfo->GetRegisterGlobals().'<br>';
$rs .= '安全模式 safe_mode：'.$ServerInfo->GetSafeMode().'<br>';
$rs .= '图形处理 GD Library：'.$ServerInfo->GetGdVersion().'<br>';
$rs .= '内存占用：'.$ServerInfo->GetMemoryUsage().'<br>';
echo $rs;
?>