<?php
/**
 * hash类，主要处理类
 * @author prg
 * @copyright 2014
 * @uses Severs
 * @uses Skey
 */
class FlexiHash
{
	private $keyHash;
	function __construct()
	{
		
	}
	
	/**
	 *ֵ生成对应的hash值
	 * @param string $key
	 * @return boolean
	 */
	function hash($key)
	{
		$md5  = substr(md5($key),0,8);
		$seed = 31;
		$hash = 0;
		for($i=0;$i<8;$i++)
		{
			$hash = $hash*$seed+ord(md5($i));
		}
		return $hash & 0x7FFFFFFF;
	}
}
?>