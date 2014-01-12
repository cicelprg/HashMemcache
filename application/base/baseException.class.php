<?php
/**
 * 异常处理类
 * @author prg
 * @copyright 2014
 */
class BaseException extends Exception
{
	function __toString()
	{
		return "<script type='text/javascript'>
				alert('".$this->getMessage()."')
				</script>";
	}
}
?>