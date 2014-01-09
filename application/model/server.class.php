<?php
/**
 * severs model
 * @author prg
 * @copyright 2014
 * @uses mysql.class.php
 */
require_once '../base/mysql.class.php';
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
		$query = 'select * from t_severs';
		$res   = BaseMysql::$db->query($query);
		for($i=0;$i<$res->num_rows;$i++)
		{
			$row = $res->fetch_assoc();
			$this->severList[$i] = $row['shost'];
			$this->severList[$i] = $row['sport'];
			$this->severList[$i] = $row['shash'];
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
		$query = "insert into t_severs(shost,sport,shash) values(?,?,?)";
		$stmt  = BaseMysql::$db->prepare($query);
		$stmt->bind_param("sii",$data['shost'],$data['sport'],$data['shash']);
		if($stmt->execute())
		{
			return true;
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
		$query = "delete from t_severs where sid= ?";
		$stmt  = BaseMysql::$db->prepare($query);
		$stmt->bind_param("i",(int)$id);
		if($stmt->execute())
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