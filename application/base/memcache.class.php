<?php
/**
 * memcache 连接类 
 * @author prg
 * @copyright 2014
 */
class BaseMemcahce
{
	public static $mem=array();
	/**
	 * 连接memcache
	 * @param array $sever
	 * @return boolean
	 */
	static function connect($sever=array())
	{
		
		if(is_array($sever))
		{
			if(!@isset(self::$mem[$sever['shash']]))
			{
				self::$mem[$sever['shash']] = new Memcache();
			}
			if(!@self::$mem[$sever['shash']]->connect($sever['shost'],$sever['sport'],3))
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
	 * 断开单个连接 
	 * @return boolean
	 */
	static function close($server=array())
	{
		if(isset(self::$mem[$server['shash']]))
		{
			if(@self::$mem[$server['shash']]->close())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
	 * 关闭所有的连接
	 */
	static function closeAll()
	{
		if(is_array(self::$mem)&&!empty(self::$mem))
		{
			foreach(self::$mem as $m)
			{
				$m->close();
			}
		}
	}
}
?>