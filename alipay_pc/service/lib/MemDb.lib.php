<?php
require_once 'lib/memcached.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'configs/config.php';
class MemDb{
	private $memcached;
	function __construct(){
		$this->memcached = new Memecached();	
	}
	
	function setConnect($arr_memcache)
	{
		$this->memcached = new Memecached();
		$this->connectMemcached($arr_memcache);
	}
	
	function connectMemcached($servers){
		$result = $this->memcached->addServers($servers);
		if(!$result){
			log::write("MemDb::connectMemcached():addServers() failed", "log");
			return false;
		}
		return true;
	}
	
	function setSearchResult($sql, $ret, $expiration = 0, $flag = MEMCACHE_COMPRESSED){
		if(empty($sql)){
			return false;
		}
		$key = md5($sql);
		
		$result = $this->memcached->get($key);
		if(!$result){
			$result = $this->memcached->set($key, $ret, $flag, $expiration);
		}else{
			$result = $this->memcached->replace($key, $ret, $flag, $expiration);
		}
		if(!$result){
			log::write("Memecached::setSearchResult():set()/replace() key:".$key."  ret:".$ret." failed", "log");
			return false;
		}
		return true;
	}
	
	function getSearchResult($sql){
		if(empty($sql)){
			return false;
		}
		$key = md5($sql);
		$result = $this->memcached->get($key);
		if(!$result){
			log::write("MemDb::getSearchResult():get() key:".$key." failed", "log");
			return false;
		}
		return $result;
	}
	
	public function clearMemcache(){
		$result = $this->memcached->flush();
		if(!$result){
			log::write("MemDb::clearMemcach() failed", "log");
			return false;
		}
		return true;
	}
}

function  unit_coolxiu_memcached_test(){
	$memcached = new MemDb();
	global $g_arr_memcache;
	$memcached->connectMemcached($g_arr_memcache['memcache']);
	$memcached->setSearchResult("AAAA", "BBBB");
	$result = $memcached->getSearchResult("AAAA");
	echo $result;
}

//unit_coolxiu_memcached_test();
?>
