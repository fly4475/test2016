<?php

class CRedis
{
	private $_redis;
	public function __construct($configOpt)
	{
		$this->_redis = new Redis();
		
		$this->_redis->connect($configOpt['host'], $configOpt['port']);
	}
	
	public function reconnect($host, $port)
	{
		$this->_redis = new Redis();
		$this->_redis->connect($configOpt['host'], $configOpt['port']);
	}
	/**
	 * 返回redis当前数据库的记录总数
	 */
	public function dbSize()
	{
		return $this->_redis->dbsize();
	}
	
	/**
	 *  string: Redis::REDIS_STRING
	 *	set: Redis::REDIS_SET
	 *	list: Redis::REDIS_LIST
	 *	zset: Redis::REDIS_ZSET
	 *	hash: Redis::REDIS_HASH
	 *	other: Redis::REDIS_NOT_FOUND
	 * @param unknown_type $key
	 */	
	public function typeOf($key)
	{
		return $this->_redis->type($key);
	}
	
	/**
	 * 将数据同步保存到磁盘
	 */
	public function save()
	{
		return $this->_redis->save();
	}
	/**
	 * bgsave
	 * 将数据异步保存到磁盘
	 */
	public function bgSave()
	{
		return $this->_redis->bgsave();
	}
	
	/**
	 * lastSave
	 * 返回上次成功将数据保存到磁盘的Unix时戳
	 */
	public function lastSave()
	{
		return $this->_redis->lastSave();
	}
	
	/**
	 * info
	 * 返回redis的版本信息等详情
	 */
	public function info()
	{
		return $this->_redis->info();
	}
	
	/**
	 * 选择从机 
	 * @param unknown_type $host
	 * @param unknown_type $port
	 */
	public function slaveOf($host, $port){
		$redis->slaveof($host, $port);
	} 
	/**
	 * 清除所有数据库
	 */
	public function clearAll()
	{
		return $this->_redis->flushAll();
	}
	/**
	 * 随机返回一个值
	 */
	public function randomValue()
	{
		$key = $this->_redis->randomKey();
		return  $this->_redis->get($key);
	}
	
	/**
	 * 随机反回一个key
	 */
	public function randomKey()
	{
		return $this->_redis->randomKey();
	}
	
	/**
	 * 清楚当前库
	 */
	public function clear()
	{
		return $this->_redis->flushDB();
	}
	/**
	 * 
	 * 清空选择的数据库
	 * @param unknown_type $db
	 */
	public function clearDb($db)
	{
		$this->_redis->select($db);
		return $this->_redis->flushDB();
	}
	
	public function setValue($key, $value)
	{
		return $this->_redis->set($key, serialize($value));
	}
	
	public function getValue($key)
	{
		return unserialize($this->_redis->get($key));
	}
	
	/**
	 * 设置时长
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $time
	 */
	public function setex($key, $value, $time)
	{
		return $this->_redis->setex($key, $time, $value);
	}
	/**
	 * 生存时长
	 * @param unknown_type $key
	 */
	public function getex($key)
	{
		return $this->_redis->ttl('key');
	}
	/**
	 * 判断存在
	 * @param unknown_type $key
	 */
	public function isExist($key)
	{
		return $this->_redis->exists($key);
	}
	/**
	 * 删除指定key
	 * @param unknown_type $key
	 */
	public function delete($key)
	{
		return $this->_redis->del($key);
	}
		
	/**
	 * 左入队
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function lpush($key, $value)
	{
		return $this->_redis->lpush($key, $value);
	}
	
	/**
	 *  左出队
	 * @param unknown_type $key
	 */
	public function lpop($key)
	{
		return $this->_redis->lpop($key);
	}
	
	/**
	 * 右入队
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function rpush($key, $value)
	{
		return $this->_redis->rpush($key, $value);
	}
	
	/**
	 *  右出队
	 * @param unknown_type $key
	 */
	public function rpop($key)
	{
		return $this->_redis->rpop($key);
	}
	
	/**
	 * 自增key
	 * @param unknown_type $key
	 */
	public function incr($key)
	{
		return $this->_redis->incr($key);
	}
}