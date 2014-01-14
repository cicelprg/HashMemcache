<?php
/**
 * severs model
 * @author prg
 * @copyright 2014
 * @uses mysql.class.php
 */
require_once $GLOBALS['memcached_path'].'/base/mysql.class.php';
class Severs
{
	/**
	 * 服务器列表
	 * @var array 
	 */
	private $severList = array(array());
	
	/**
	 * 连接mysql
	 */
	function __construct()
	{
		BaseMysql::connect();
	}
	
	/**
	 * 获取所有的服务器
	 * @return multitype:
	 */
	function getAllServers()
	{
		$query = 'select * from '.$GLOBALS['t_server'];
		$res   = BaseMysql::$db->query($query);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$row = $res->fetch_assoc();
			$this->severList[$i]['sid']   = (int)$row['sid'];
			$this->severList[$i]['shost'] = $row['shost'];
			$this->severList[$i]['sport'] = (int)$row['sport'];
			$this->severList[$i]['shash'] = (int)$row['shash'];
		}
		return $this->severList;
	}
	
	/**
	 * 增加服务器
	 * @param array $data
	 * @return boolean
	 */
	function addSever($data = array())
	{
		if($this->checkServerExist($data))
		{
			return -1;
		}
		$query = "insert into ".$GLOBALS['t_server']."(shost,sport,shash) values(?,?,?)";
		$stmt  = BaseMysql::$db->prepare($query);
		$stmt->bind_param("sii",$data['shost'],$data['sport'],$data['shash']);
		if($stmt->execute())
		{
			return $stmt->insert_id;
		}
		return false;
	}
	
	/**
	 * 删除一个服务器
	 * @param int $id
	 * @return boolean
	 */
	function removeSever($id)
	{
		//var_dump($id);
		$query = "delete from ".$GLOBALS['t_server']." where sid=".$id;
		$res   = BaseMysql::$db->query($query);
		if($res)
		{
			return true;
		}
		return false;
	}
	
	function checkServerExist($server=array())
	{
		$query = "select * from ".$GLOBALS['t_server']." where shash=".$server['shash'];
		$res = BaseMysql::$db->query($query);
		if($res->num_rows>0)
		{
			return true;
		}
		return false;		
	}
	/**
	 * 断开mysql
	 */
	function __destruct()
	{
		BaseMysql::close();
	}
}
?>