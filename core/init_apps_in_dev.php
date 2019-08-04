<?php

/**
 * 根据配置获取需要的启动文件
 * @param $config
 *
 * @return array
 * @Author  : linge@9rax.com
 * @DateTime: 2018/7/29 13:27
 */
function get_bat_files($config){

    $type=implode('',$config[1]);

    $files=[];

    if(strpos($type,'socket')!==false){
        $files[]='businessworker';
        $files[]='gateway';
        $files[]='register';
    }

    if(strpos($type,'http')!==false){
        $files[]='web';
    }

    if(strpos($type,'queue')!==false){
        $files[]='queue';
    }


    foreach ($files as &$file){
        $file="start_".$file.".php";
    }

    return $files;
}


/**
 * 生成windows bat文件
 * @param $config
 * @param $name
 *
 * @Author  : linge@9rax.com
 * @DateTime: 2018/7/29 13:28
 */
function build_bat_file($config,$name){

    global $argv;

    $bat=dirname(__DIR__).DIRECTORY_SEPARATOR.$config[0].DIRECTORY_SEPARATOR.'start_win_'.$name.'.cmd';

    //if(!file_exists($bat) || strpos(implode('',$argv),'-f')!==false){

        $files=glob(dirname(__DIR__).DIRECTORY_SEPARATOR.$config[0].DIRECTORY_SEPARATOR.'/client_service/start*.php');

        $SERVER_PATH =dirname(__DIR__).DIRECTORY_SEPARATOR.$config[0].DIRECTORY_SEPARATOR.'client_service';

        $str ='';


        foreach ($files as $file){
            if(is_file($file)){
                $str .='   '.$file;
            }
        }

        $command="";

        $watch=strpos(implode('',$argv),'nodemon')!==false;

        if($watch){
            $command='nodemon -w "*" -e "php" -x "';
        }

        $command.='php '.$str.' start';

        $command.=' '.($watch?'"':'').PHP_EOL;

        $command.='pause;';

        file_put_contents($bat,$command);

}


/**
 * 创建文件夹
 * @param      $path
 * @param bool $real_path
 *
 * @Author  : zaoyongvip@gmail.com
 * @DateTime: 2018/7/29 13:18
 */
function _mkdir($path,$real_path=false){
    $path=$real_path?$path:dirname(__DIR__).DIRECTORY_SEPARATOR.$path;
    if(!is_dir($path)){
        @mkdir($path,0777,true);
    }
}

/**
 * 文件夹复制
 * @param $src
 * @param $dst
 * @Author: zaoyongvip@gmail.com
 */
function copy_dir($src, $dst)
{  // 原目录，复制到的目录
    $dir = opendir($src);
    !is_dir($dst) && mkdir($dst, 0777, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copy_dir($src . '/' . $file, $dst . '/' . $file);
            } else {
                if(!file_exists($dst . '/' . $file)){
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    }
    closedir($dir);
}


/**
 * 初始化项目目录
 * @param $config
 *
 * @Author  : linge@9rax.com
 * @DateTime: 2018/7/29 13:26
 */
function init_app($config){
    //创建APP目录
    _mkdir($config[0]);

    $from_dir=__DIR__.DIRECTORY_SEPARATOR.'_app_';

    $to_dir =dirname(__DIR__).DIRECTORY_SEPARATOR.$config[0];

    copy_dir($from_dir,$to_dir);

    //删除不需要的原装启动文件
    $files=glob(dirname(__DIR__).'/'.$config[0].'/client_service/start*.php');

    $start_files=get_bat_files($config);

    foreach ($files as $file){

        if(!in_array(basename($file),$start_files) && in_array(basename($file),['start_businessworker.php','start_gateway.php','start_register.php','start_web.php','start_queue.php'])){
            unlink($file);
        }
    }

}



$apps=include dirname(__DIR__).'/apps.config.php';

foreach ($apps as $name=>$app){
    init_app($app);
    build_bat_file($app,$name);
}

