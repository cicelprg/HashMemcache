<?php
/**
 * hash类，主要处理类
 * @author prg
 * @copyright 2014
 * @uses Severs
 * @uses Skey
 */
require_once '../model/server.class.php';
require_once '../model/skey.class.php';
require_once '../base/memcache.class.php';
require_once '../base/baseException.class.php';
class FlexiHash
{
	private $hashKey;
	private $severs ;
	private $severModel;
	private $skeyModel;
	private $isSort = false;
	function __construct()
	{
		$this->severModel = new Severs();
		$this->severs     = $this->severModel->getAllServers();
		$this->sortRbyHash();
		$this->isSort = true;
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
			$hash = $hash*$seed+ord($md5{$i});
		}
		return $hash & 0x7FFFFFFF;
	}
	
	/**
	 * 
	 * @param array $sever
	 */
	function addSever($sever =array())
	{
		if(is_array($sever))
		{
			$this->isSort = false;
			$key = $this->hash(implode('', $sever));
			$sever['shash'] = $key;
			//添加到数据库中
			$this->severModel->addSever($sever);
			
			//这里改变存储结构
			if(empty($this->severs[0]))
			{
				$this->severs[0] = $sever;
			}
			else
			{
				$this->severs[] = $sever;
			}
			if(!$this->isSort)
			{
				$this->sortRbyHash();
			}
			
			
		}
	}
	
	/**
	 * 获取服务器列表
	 * @return Ambigous <array, unknown, boolean, multitype:, multitype:>
	 */
	function getAllSevers()
	{
		return $this->severs;
	}
	
	/**
	 * 对服务器 进行平排序，按照服务器的hash值降序
	 */
	function sortRbyHash()
	{
		$num = count($this->severs);
		//var_dump($this->severs);
		for($i=0;$i<$num-1;$i++)
		{
			for($j=$i+1;$j<$num;$j++)
			{
				if($this->severs[$i]['shash']<$this->severs[$j]['shash'])
				{
					$tmp = $this->severs[$i];
					$this->severs[$i] = $this->severs[$j];
					$this->severs[$j] = $tmp;
				}
			}
		}
		$this->isSort = true;	
	}
	
	/**
	 * 获取key 要保存到那个服务器
	 * @param string $key
	 * @return array
	 */
	function getSaveSever($key)
	{
		if(!$this->isSort)
		{
			$this->sortRbyHash();
		}
		$this->hashKey = $this->hash($key);
		foreach ($this->severs as $sever)
		{
			if($this->hashKey>=$sever['shash'])
			{
				return $sever;
			}
		}
		//这里没有找到对应的sever时返回最后一个,就是hashKey 小于了服务器最小的那个hash值
		return $this->severs[count($this->severs)-1];
	}
	
	function saveValue($key,$value)
	{
		try 
		{
			$sever = $this->getSaveSever($key);
			var_dump($this->hashKey);
			var_dump($sever);
			if(!is_array($sever))
			{
				throw new BaseException("对不起，获取要保存的服务器失败");
			}
			if(!BaseMemcahce::connect($sever))
			{
				throw new BaseException('对不起，服务器'.$sever['shost'].':'.$sever['sport'].'连接失败，请检查是否开启服务！');
			}
			//var_dump($this->hashKey);
			if(!BaseMemcahce::$mem->set($this->hashKey,serialize($value)))
			{
				throw new BaseException('对不起，数据在'.$sever['shost'].':'.$sever['sport'].'服务器上保存失败 ');
			}
			//echo BaseMemcahce::$mem->getVersion();
			BaseMemcahce::close();
		}
		catch (BaseException $be)
		{
			echo $be;
		}
		
	}
}
?>