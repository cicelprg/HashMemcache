<?php
/**
 * 配置文件，这里直接用最简单的配置
 * @package config
 */

/**
 *mysql 数据库配置
 */
$GLOBALS['db_host'] = 'localhost';
$GLOBALS['db_user'] = 'root';
$GLOBALS['db_pwd' ] = '245030109';
$GLOBALS['db_name'] = 'memcached';

/**
 * mysql 表名称配置
 */
$GLOBALS['t_server'] ='t_severs';
$GLOBALS['t_skey']   ='t_skey';

/**
 * 这里配置memcached基本路径 用户使用时要修改这里配置和model下面的FlexiHash.php 里面的第一个引入config文件的路径
 */
$GLOBALS['memcached_path'] =realpath($_SERVER['DOCUMENT_ROOT']).'/HashMemcache/application';
?>