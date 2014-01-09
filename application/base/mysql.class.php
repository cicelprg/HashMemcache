<?php
/**
 * mysql 连接类
 * @author prg
 * @copyright 2014
 */
require_once '../config/config.php';
class BaseMysql
{
	/**
	 * 
	 * @var resource
	 */
	public static $db;
	/**
	 * 连接
	 */
	static function connect()
	{
		if(!isset(self::$db))
		{
			self::$db = new mysqli($GLOBALS['db_host'],$GLOBALS['db_user'],$GLOBALS['db_pwd'],$GLOBALS['db_name']);
		}
	}
	
	/**
	 * 断开连接
	 */
	static function close()
	{
		if(isset(self::$db))
		{
			self::$db->close();
		}
	}
}
?>