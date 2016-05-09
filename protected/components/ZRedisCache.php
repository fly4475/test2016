<?php
class ZRedisCache extends CRedisCache{

	/**
	 * 左侧压入一个数据
	 * @param String $key
	 * @param String $value
	 * @return int
	 */
	public function lpush($key,$value){
		if(!is_string($value)){
			throw new Exception('The value type is not a string.If u want to push an array data.You can use JSON.');
		}
		$num = $this->executeCommand('LPUSH',array($key,$value));
		return intval($num);
	}
	
	public function exists($key){
		if(!is_string($key)){
			throw new Exception('The value type is not a string.If u want to push an array data.You can use JSON.');
		}
		$result = $this->executeCommand('EXISTS',array($key));
		return $result;
	}
	
	public function setRedis($key,$value,$expire)
	{
		if ($expire==0)
			return (bool)$this->executeCommand('SET',array($key,$value));
		return (bool)$this->executeCommand('SETEX',array($key,$expire,$value));
	}
	
	public function getRedis($key)
	{
		return $this->getValue($key);
	}
	
	public function delete($key)
	{
		return $this->deleteValue($key);
	}
}