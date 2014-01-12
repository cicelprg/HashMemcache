<?php
/**
 * memcache 连接类 
 * @author prg
 * @copyright 2014
 */
class BaseMemcahce
{
	public static $mem;
	/**
	 * 连接memcache
	 * @param array $sever
	 * @return boolean
	 */
	static function connect($sever=array())
	{
		if(isset(self::$mem))
		{
			self::close();
		}
		else
		{
			self::$mem = new Memcache();
		}
		if(is_array($sever))
		{
			if(!@self::$mem->connect($sever['shost'],$sever['sport'],3))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	   	return false;
	}
	
	/**
	 * 断开连接 
	 * @return boolean
	 */
	static function close()
	{
		if(isset(self::$mem))
		{
			if(@self::$mem->close())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
}
?>