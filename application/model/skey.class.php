<?php
/**
 * skey model
 * @author prg
 * @copyright 2014 
 */
require_once $GLOBALS['memcached_path'].'/base/mysql.class.php';
class Skey
{
	/**
	 *某个服务器对应的keyֵ
	 * @var array
	 */
	private $keys = array();
	
	function __construct()
	{
		BaseMysql::connect();
	}
	
	/**
	 * 增加一个服务器所保存的keyֵ
	 * @param array $data
	 * @return boolean
	 */
	function addServerkey($data =array())
	{
		//var_dump($data);
		if($this->CheckTheKey($data))
		{
			return true;
		}
		if(is_array($data))
		{
			$query = "insert into t_skey(sid,skey) values(?,?)";
			$stmt  = BaseMysql::$db->prepare($query);
			$stmt->bind_param("ii",$data['sid'],$data['skey']);
			if($stmt->execute())
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 获取某个服务器所保存的所有key，数据转存
	 * @param int $sid
	 * @return multitype:
	 */
	function getKeysBySid($sid)
	{
		$query = "select skey from t_skey where sid=".(int)$sid;
		$res   = BaseMysql::$db->query($query);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$row          = $res->fetch_assoc();
			$this->keys[] = (int)$row['skey'];
		}
		return $this->keys;
	}
	
	/**
	 * 删除某个服务器上的keyֵ
	 * @param  int $sid
	 * @return boolean
	 */
	function deleteKeysBySid($sid)
	{
		//var_dump($sid);
		$query = "delete from t_skey where sid =".$sid;
		$res   = BaseMysql::$db->query($query);
		if($res)
		{
			return true;
		}
		return false;
			
	}
	
	/**
	 * 检查key值实在此服务器上已经保存过了
	 * @param array $data
	 * @return boolean
	 */
	function CheckTheKey($data =array())
	{
		if(is_array($data))
		{
			$query = "select kid from t_skey where sid=".$data['sid']." and skey=".$data['skey'];
			$res   = BaseMysql::$db->query($query);
			if($res->num_rows>0)
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
	 * 数据转存后批量增加 
	 * @param  $keys
	 * @param  $sid
	 */
	function addTransKeys($keys,$sid)
	{
		if(is_array($keys))
		{
			foreach($keys as $key)
			{
				$query = "insert into t_skey(sid,skey) values($sid,$key)";
				BaseMysql::$db->query($query);
			}
		}
	}
	function __destruct()
	{
		BaseMysql::close();
	}
}
?>