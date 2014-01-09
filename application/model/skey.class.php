<?php
/**
 * skey model
 * @author prg
 * @copyright 2014 
 */
require_once '../base/mysql.class.php';
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
		if(is_array($data))
		{
			$query = "insert into t_skey(sid,skey) values(?,?)";
			$stmt  = BaseMysql::$db->prepare($query);
			$stmt->bind_param("ii",(int)$data[0],(int)$data[1]);
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
		$query = "delete from t_skey where sid=?";
		$stmt  = BaseMysql::$db->prepare($query);
		$stmt->bind_param("i",(int)$sid);
		if($stmt->execute())
		{
			return true;
		}
		return false;
	}
	
	function __destruct()
	{
		BaseMysql::close();
	}
}
?>