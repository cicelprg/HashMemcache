<?php
/**
 * hash类，主要处理类
 * @author prg
 * @copyright 2014
 * @uses Severs
 * @uses Skey
 */
require_once realpath($_SERVER['DOCUMENT_ROOT']).'/HashMemcache/application/config/config.php';
require_once $GLOBALS['memcached_path'].'/model/server.class.php';
require_once $GLOBALS['memcached_path'].'/model/skey.class.php';
require_once $GLOBALS['memcached_path'].'/base/memcache.class.php';
require_once $GLOBALS['memcached_path'].'/base/baseException.class.php';
class FlexiHash
{
	/**
	 * 是否采用数据转存模式，会存储sid-key到数据库
	 * 会加大对数据库的操作
	 * @var bool
	 */
	private $safe = true;
	/**
	 * key的hash值
	 * @var int
	 */
	private $hashKey;
	/**
	 * 服务器列表
	 * @var array
	 */
	private $severs ;
	/**
	 * 服务器模型
	 * @var obj
	 */
	private $severModel;
	/**
	 * sid-key 模型
	 * @var obj
	 */
	private $skeyModel;
	/**
	 *标记是否排序
	 */
	private $isSort = false;
	/**
	 * 标记被移除的服务器在serverlist中的位置
	 * @var int
	 */
	private $flag;
	function __construct()
	{
		$this->severModel = new Severs();
		$this->skeyModel  = new Skey();
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
		try
		{
			if(is_array($sever))
			{
				$this->isSort = false;
				$key = $this->hash(implode('', $sever));
				$sever['shash'] = $key;
				//首先检查新天的服务器是否能够连接
				if(!BaseMemcahce::connect($sever))
				{
					throw new BaseException('对不起不能连接到新服务器，添加失败');
				}
				else
				{
					//添加到数据库中
					$sid = $this->severModel->addSever($sever);
					if(!$sid)
					{
						throw new BaseException('对不起，系统内部出错!请稍后再试');
					}
					else if($sid==-1)
					{
						throw new BaseException("服务器".$sever['shost'].':'.$sever['sport']."已经存在了！");
					}
					else
					{
						
						$sever['sid'] = (int)$sid;
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
			}
			else
			{
				throw new BaseException('服务器不符合标准 ');
			}
		}
		catch (BaseException $be)
		{
			echo $be;
			//exit;
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
		if(empty($this->severs)||empty($this->severs[0])) //这里服务器一个都没有
		{
			return false;
		}
		$this->hashKey = $this->hash($key);
		foreach ($this->severs as $sever)
		{
			if($this->hashKey>=$sever['shash'])
			{
				return $sever;
			}
		}
		//这里没有找到对应的sever时返回第一个,就是hashKey小于了服务器最小的那个hash值,此时直接轮回
		return $this->severs[0];
	}
	
	/**
	 * 数据保存
	 * @param string $key
	 * @param string $value
	 * @throws BaseException
	 */
	function saveValue($key,$value)
	{
		$fp = fopen($GLOBALS['memcached_path'].'/log/mem.log', "a+");
		if(empty($this->severs)||empty($this->severs[0]))
		{
			$str="Sorry，No server to save Data.---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return;
		}
		$sever = $this->getSaveSever($key);
		if(!is_array($sever))
		{
			$str="Sorry，We Cannot get The server to save Data.---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return;
		}
		if(!@BaseMemcahce::connect($sever))
		{
			$str = "Sorry，Data Save Failed.We Cannot Connect to".$sever['shost'].
			':'.$sever['sport']."Plesea check open the server---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return;
		}
		if(!@BaseMemcahce::$mem[$sever['shash']]->set($this->hashKey,serialize($value)))
		{
			$str='Sorry，Data('.serialize($value).') Save Failed On'.$sever['shost'].
			':'.$sever['sport']."---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return;
		}
		else if($this->safe) //相对安全，保存对应的key值
		{
			$data = array(
					'sid' =>$sever['sid'],
					'skey'=>$this->hashKey
					);
			$this->skeyModel->addServerkey($data);
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @throws BaseException
	 * @return boolean|mixed
	 */
	function getValue($key)
	{
		$fp = fopen($GLOBALS['memcached_path'].'/log/mem.log', "a+");
		if(empty($this->severs)||empty($this->severs[0]))
		{
			$str="Sorry，No server to get Data.---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return;
		}
		$server = $this->getSaveSever($key);
		var_dump($server);
		if(!BaseMemcahce::connect($server))
		{
			//这里命中率降低 //这里也只能写日志
			//throw new BaseException("读取数据失败，不能连接到对应的服务器".$server['shost'].':'.$server['port']);
			$str='Sorry，Read Data Failed.Cannot connect to'.$server['shost'].
			':'.$server['sport']."---Time:".date("Y-m-d H:i:s",time())."\r\n";
			fputs($fp, $str);
			fclose($fp);
			return false;
		}
		else
		{
			$res = BaseMemcahce::$mem[$server['shash']]->get($this->hashKey);
			if(!$res)
			{
				return false;
			}
			else
			{
				//命中率变高
				return unserialize($res);
			}
		}
			
	}
	/**
	 * 删除服务器时对数据的转存
	 * @param array $server
	 */
	function DataTransfer($server=array(),$tranServer =array())
	{
		try 
		{
			$allkeys  = $this->skeyModel->getKeysBySid($server['sid']);
			//var_dump($allkeys);
			$num = count($allkeys);
			$skid = array();
			//连接到服务器
			if(!BaseMemcahce::connect($server))
			{
				throw new BaseException("warning:，不能连接要关闭的服务器".$server['shost'].':'.$server['sport']."不能进行数据转存!请关闭安全然后删除服务器");
			}
			if(!BaseMemcahce::connect($tranServer))
			{
				throw new BaseException("Error:不能连接到目标服务器".$server['shost'].':'.$server['sport'].'请保证服务器打开 ');
			}
			$k = 0;
			//开始数据转存
			for($i=0;$i<$num;$i++)
			{
				$v = BaseMemcahce::$mem[$server['shash']]->get($allkeys[$i]);
				if($v)
				{
					//var_dump($v);
					if(BaseMemcahce::$mem[$tranServer['shash']]->set($allkeys[$i],$v))
					{
						$skid[] = $allkeys[$i];
						$k++;
					}
				}
			}
			
			//flushall
			BaseMemcahce::$mem[$server['shash']]->flush();
			//var_dump($skid);
			//这里保存有效转移的数据
			if($k>0)
			{
				$this->skeyModel->addTransKeys($skid, (int)$tranServer['sid']);
			}
			$data[$num] = $k;
			return $data;
		}
		catch (BaseException $be)
		{
			echo $be;
		}
	}
	
	/**
	 * 删除服务器 
	 * @param array $server
	 * @throws BaseException
	 * @return number
	 */
	function removeServer($server =array())
	{
		try
		{
			if(count($this->severs)==1)
			{
				throw new BaseException("只有一个服务器，不能移除 ");
			}
			if(!$this->isSort)
			{
				$this->sortRbyHash();
			}
			if($this->safe)
			{
				$tranServer = $this->getTransToServer($server);
				//var_dump($tranServer);
				//exit;
				if($tranServer == $server)
				{
					throw new BaseException("只有一个服务器，不能移除 ");
				}
				$data = $this->DataTransfer($server,$tranServer);
			}
			//这里移除成功 
			$this->skeyModel->deleteKeysBySid($server['sid']);
			$this->severModel->removeSever($server['sid']);
			//var_dump($this->flag);
			array_splice($this->severs, $this->flag,1);
			$this->isSort =false;
			if($this->safe)
			{
				return $data;
			}
		}catch (BaseException $be)
		{
			echo $be;
		}
		
	}
	
	/**
	 * 当一个服务器移除是，该服务器上的数据直接转移到hash环上的下一个服务器
	 * @param array $server
	 */
	function getTransToServer($server =array())
	{
		$num = count($this->severs);
		for($i=0;$i<$num;$i++)
		{
			if($this->severs[$i]['shash']==(int)$server['shash']) //这里找到要移除的服务器 
			{
				$this->flag = $i;
				return $this->severs[($i+1)%$num];
			}
		}
	}
	
	/**
	 * 设置是否安全
	 * @param bool $value
	 */
	function setSafe($value)
	{
		if(is_bool($value))
		{
			$this->safe = $value;
		}
	}
	
	/**
	 * 获取安全状态
	 * @return boolean
	 */
	function getSafe()
	{
		return $this->safe;
	}
}
?>