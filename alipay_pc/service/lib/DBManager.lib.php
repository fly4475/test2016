<?php
//require_once '../configs/config.php';
require_once 'mySql.lib.php';
require_once 'WriteLog.lib.php';

abstract class DBManager {
	var $rsp_count;
	var $sql_conn;
	
	const SQL_EXCUTE_ERROR = 255;
	
	function __construct(){
		$this->rsp_count = 0;
	}
	
	function connectMySql() {
		try {
			global $g_arr_db_conn;
			$this->sql_conn = new mysql($g_arr_db_conn['host'],
					$g_arr_db_conn['user'],
					$g_arr_db_conn['pwd'],
					$g_arr_db_conn['db'],
					"",
					$g_arr_db_conn['coding']);
		} catch (Exception $e) {
			Log::write("DBManager::connectMySql(".$g_arr_db_conn['db'].") error","log");
			return false;
		}
		return true;
	}
	
	function connectMySqlPara($g_arr_db_conn) {
		try {
			if(($this->sql_conn != null) && $this->sql_conn->isConnecting){
				return true;
			}
			//global $g_arr_db_conn;
			$this->sql_conn = new mysql($g_arr_db_conn['host'],
					$g_arr_db_conn['user'],
					$g_arr_db_conn['pwd'],
					$g_arr_db_conn['db'],
					$g_arr_db_conn['type'],
					$g_arr_db_conn['coding']);
		} catch (Exception $e) {
			Log::write("DBManager::connectMySqlPara(".$g_arr_db_conn['db'].") error","log");
			return false;
		}
		return true;
	}
	/**
	 * 执行查询语句，返回结果数组
	 * @param unknown_type $sql
	 */	

	function executeQuery($sql){
		try{
			if(!$this->sql_conn->isConnecting){
				Log::write("DBManager::executeQuery():sql_conn->isConnecting is false", "log");
				return  false;
			}
			
// 			$sql = iconv('gb2312', 'utf-8', $sql);
			$result = $this->sql_conn->commit_query($sql);
			if($result === false){
				Log::write("DBManager::executeQuery():commit_query() sql:".$sql." failed", "log");
				return  false;
			}
				
			$this->rsp_count = $this->sql_conn->commit_db_num_rows();
			$rows = $this->sql_conn->commit_fetch_assoc_rows();
		}catch (Exception $e){
			Log::write("DBManager::executeQuery()exception : ".$e->getMessage(), "log");
			return  false;
		}
		return  $rows;
	}
	
	function getQueryCount(){
		return $this->rsp_count;
	}
	/**
	 * 执行统计返回统计结果
	 * @param unknown_type $sql
	 */
	function executeScan($sql){
		try{
			if(!$this->sql_conn->isConnecting){
				Log::write("DBManager::executeQuery():sql_conn->isConnecting is false", "log");
				return  false;
			}
// 			$sql = iconv('gb2312', 'utf-8', $sql);
			$result = $this->sql_conn->commit_query($sql);
			if(!$result){
				Log::write("DBManager::executeScan():commit_query()".$sql." error", "log");
				return false;
			}
			list($count) = $this->sql_conn->commit_fetch_row();
		}catch (Exception $e){
			Log::write("DBManager::executeScan()exception : ".$e->getMessage(), "log");
			return  false;
		}		
		return $count;
	}
	
	/**
	 * 执行插入、修改等SQL语句
	 * @param unknown_type $sql
	 */
	function executeSql($sql){
		try{
			if(!$this->sql_conn->isConnecting){
				Log::write("DBManager::executeSql():sql_conn->isConnecting is false", "log");
				return  false;
			}
// 			$sql = iconv('gb2312', 'utf-8', $sql);
			$result = $this->sql_conn->commit_query($sql);
			if(!$result){
				Log::write("DBManager::executeSql():commit_query() sql:".$sql." failed", "log");
				return  false;
			}
			
			if($this->sql_conn->commit_errno()){
				Log::write("DBManager::executeSql(".$coolxiu->$s_name.") failed", "log");
				$this->sql_conn->commit_rollback();
				$this->sql_conn->commit_end();
				return false;
			}
		}catch (Exception $e){
			Log::write("DBManager::executeSql()exception : ".$e->getMessage(), "log");
			return  false;
		}
		return true;
	}
	
	public function beginTransaction()
	{
		$this->sql_conn->commit_start();
	}
	
	public function commit_error()
	{
		return $this->conn->errno;
	}
	
	public function endTransaction()
	{
		$this->sql_conn->commit_end();
	}
	
	public function roolback()
	{
		$this->sql_conn->commit_rollback();
		$this->sql_conn->commit_end();
	}
	
	public function commit()
	{
		$this->sql_conn->commit();
		$this->sql_conn->commit_end();
	}
	
	function lockTable($table){
		$sql = sprintf("LOCK TABLES %s WRITE", $table);
		
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("DBManager::lockTable():executeQuery() sql:".$sql." failed", "log");
			return  false;
		}
		return true;
	}
	
	function unlockTables(){
		$sql = "UNLOCK TABLES";
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("DBManager::unlockTables():executeQuery() sql:".$sql." failed", "log");
			return  false;
		}
		return true;
	}
	
	function disconnectMySql(){
		if($this->sql_conn){
			$this->sql_conn->__destruct();
		}
	}
}
?>