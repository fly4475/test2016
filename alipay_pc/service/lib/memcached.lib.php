<?php
require_once 'lib/WriteLog.lib.php';
class Memecached{
	
	private $memcache;
	function __construct(){
		$this->memcache = new Memcache();
	}
	
	function addServer($host, $port, $persistent = true, $weight= 1){
		$result = $this->memcache->addServer($host, $port, $persistent, $weight);
		if(!$result){
			log::write("Memecached::addServer() failed", "log");
		}
// 		$version = $this->memcache->getVersion();
// 		echo "Server's version: ".$version."\n";
		return $result;
	}
	
	/**
	 * 向连接池增加多个链接
	 * array('host'=>'127.0.0.1', 'port'=>'11211', 'persistent'=>true,'weight'=>0),
	 * @param unknown_type $servers
	 */	
	function addServers($servers ){
		foreach ($servers as $server){
			$this->addServer($server['host'], $server['port'], $server['persistent'], $server['weight']);
		}
		return true;
	}
	
	function connect($host , $port, $timeout = 1){
		$result = $this->memcache->connect($host , $port, $timeout);		
		if(!$result){
			log::write("Memecached::connect(srver:".$host."port:".$port.") failed", "log");
			return false;
		}
		return true;
	}
	
	/**
	 * 添加记录
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $expiration
	 */
	function add($key , $value, $expiration = 0){
		$result = $this->memcache->add($key , $value, $expiration);
		if(!$result){
			log::write("Memecached::add() failed", "log");
		}
		return $result;
	}

	/**
	 * 向指定服务器添加记录
	 * @param unknown_type $server_key
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $expiration
	 */
	function addByKey($server_key, $port, $key, $value, $expiration = 0){
		$result = $this->connect($server_key, $port);
		if(!$result){
			log::write("Memecached::addByKey():connect() failed", "log");
			return false;
		}
		$result = $this->memcache->add($key , $value, $expiration);
		if(!$result){
			log::write("Memecached::addByKey():add() failed", "log");
			return false;
		}
		return true;
	}
	
	function get($key){
		$result = $this->memcache->get($key);
		if(!$result){
// 			log::write("Memecached::get() failed", "log");
			return false;
		}
		return $result;
	}
	
	function getByKey ($server, $key){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::getByKey():connect() failed", "log");
			return false;
		}
		
		$result = $this->get($key);
		if(!$result){
			log::write("Memecached::getByKey():get() failed", "log");
			return false;
		}
		return $result;
	}
	
	function set($key , $var, $flag = MEMCACHE_COMPRESSED, $expire = 0){
		
		if($flag == null){
			$result = $this->memcache->set($key, $var);
		}else{
			if($expire != 0){
				$expire = time() + $expire;
			}
			$result = $this->memcache->set($key, $var, $flag, $expire);
		}		
		if(!$result){
			log::write("Memecached::set():set(key".$key.";var:".$var.") failed", "log");
			return false;
		}
		return true;
	}

	function setByKey($server, $key, $var, $flag = MEMCACHE_COMPRESSED, $expire = 0){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::setByKey():connect() failed", "log");
			return false;
		}
		
		if($expire != 0){
			$expire = time() + $expire;
		}
		$result = $this->set($key , $var, $flag, $expire);
		if(!$result){
			log::write("Memecached::setByKey():set() failed", "log");
			return false;
		}
		return true;
	}
	
	function delete($key){
		$result = $this->memcache->delete($key);		
		if(!$result){
			log::write("Memecached::delete(key:".$key.") failed", "log");
			return false;
		}
		return true;
	}
	
	function deleteByKey($server, $key){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::deleteByKey():connect() failed", "log");
			return false;
		}
		$result = $this->delete($key);
		if(!$result){
			log::write("Memecached::deleteByKey():delete() failed", "log");
			return false;
		}
		return true;
	}
	
	function flush(){
		$result = $this->memcache->flush();
		if(!$result){
			log::write("Memecached::flush():flush() failed", "log");
			return false;
		}
		return true;
	}

	function flushByKey($server){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::flushByKey():connect() failed", "log");
			return false;
		}
		$result = $this->flush();
		if(!$result){
			log::write("Memecached::flush():flush() failed", "log");
			return false;
		}
		return true;
	}
	
	function decrement($key, $value = 1){
		$result = $this->memcache->decrement($key, $value);
		if(!$result){
			log::write("Memecached::decrement():decrement() failed", "log");
			return false;
		}
		return $result;
	}
	
	function decrementByKey($server, $key, $value = 1){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::decrementByKey():connect() failed", "log");
			return false;
		}
		$result = $this->decrement($key, $value);
		if(!$result){
			log::write("Memecached::decrement():decrement() failed", "log");
			return false;
		}
		return $result;
	}
	
	function increment($key, $value = 1){
		$result = $this->increment($key, $value);
		if(!$result){
			log::write("Memecached::increment():increment() failed", "log");
			return false;
		}
		return $result;
	}
	
	function incrementByKey($server, $key, $value = 1){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::incrementByKey():connect() failed", "log");
			return false;
		}
		$result = $this->increment($key, $value);
		if(!$result){
			log::write("Memecached::increment():increment() failed", "log");
			return false;
		}
		return $result;
	}
	
	function replace($key, $var, $flag = MEMCACHE_COMPRESSED, $expire = 0){
		if($expire != 0){
			$expire = time() + $expire;
		}
		$result = $this->memcache->replace($key, $var, $flag, $expire);
		if(!$result){
			log::write("Memecached::replace():replace() failed", "log");
			return false;
		}
		return true;
	}

	function replaceByKey($server, $key, $var, $flag = MEMCACHE_COMPRESSED, $expire = 0){
		$result = $this->connect($server['host'], $server['port']);
		if(!$result){
			log::write("Memecached::replaceByKey():connect() failed", "log");
			return false;
		}
		if($expire != 0){
			$expire = time() + $expire;
		}
		$result = $this->replace($key, $var, $flag, $expire);
		if(!$result){
			log::write("Memecached::replaceByKey():replace() failed", "log");
			return false;
		}
		return true;
	}
	
	function close(){
		$result = $this->memcache->close();
		if(!$result){
			log::write("Memecached::close():close() failed", "log");
			return false;
		}
	}
}

	function unit_memcached_test(){
		$memcached = new Memecached();
		$memcached->addServer('127.0.0.1', 11211);
		$result = $memcached->get("key");
		echo $result;
	}
	
	//unit_memcached_test();
?>
