<?php
/**
 * 数据库操作具体实现
 * 
 * @author lijie1@yulong.com
 * 
 */
	require_once ('WriteLog.lib.php');
class mysql{
	
	private $db_host;  			//数据库主机
	private $db_user;  			//数据库用户名
	private $db_pwd;   			//用户密码 
	private $db_database;    	//数据库名
	private $conn;           	//数据库链接标示
	private $result;         	//查询返回结果
	private $sql;	  			//sql执行语句
	private $row;     			//返回的条目数
	private $coding;  			//数据库编码，GBK,UTF8,gb2312
	private $bulletin = true;   //是否开启错误记录
	private $show_error = true;	//测试阶段，显示所有错误,具有安全隐患,默认关闭
	private $is_error = false; 	//发现错误是否立即终止,默认true,建议不启用，因为当有问题时用户什么也看不到是很苦恼的
	var		$isConnecting = false;		

	/*构造函数*/
	function __construct($db_host, $db_user, $db_pwd, $db_database, $conn_type, $coding){
     	$this->db_host		= $db_host;
     	$this->db_user		= $db_user;
     	$this->db_pwd 		= $db_pwd;
     	$this->db_database	= $db_database;
     	$this->conn;
     	$this->conn_type 	= $conn_type;
     	$this->coding		= $coding;
     	$this->sql = "";
     	$this->connect();
    }

	/*数据库连接*/                 
 	function connect() { 
 		try{
 			if($this->conn_type=="pconn"){
 				//永久链接
 				$this->conn = mysql_pconnect($this->db_host,$this->db_user,$this->db_pwd);
 			}else
 				if($this->conn_type=="commit"){
 				//mysqli连接，实现事务处理
 				$arrTemp = explode(':', $this->db_host);//兼容旧的链接数据库处理 
 				$this->db_host = isset($arrTemp[0])?$arrTemp[0]:$this->db_host;
 				$nPort = (int)(isset($arrTemp[1])?$arrTemp[1]:3306);
	 			$this->conn = new mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_database, $nPort);
	 			if(mysqli_connect_errno()){
	 				Log::write("mysql::connect():mysqli() failed", "log");
	 				return false;
	 			}
 			}else{
 				//即时链接
 				$this->conn = mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
 			}
 			if($this->conn_type == "commit"){
 				$this->conn->query("SET NAMES $this->coding");
 				$this->isConnecting = true;
 				return true;
 			}
 			if(!mysql_select_db($this->db_database,$this->conn)){
 				if($this->show_error){
 					$this->show_error("数据库不可用", $this->db_database);
 					return false;
 				}
 			}
 				
 			mysql_query("SET NAMES $this->coding");
 			
 		}catch (Exception $e){
 			Log::write("mysql::connect() exception : ".$e->getMessage(), "log");
 			return false;
 		}
 		$this->isConnecting = true;
 		return true;
	}
	
	/*数据库执行语句，可执行查询添加修改删除等任何sql语句*/
	function query($sql){		
		if($sql == ""){
			$this->show_error("sql语句错误：","sql查询语句为空");
		} 
  	  	$this->sql = $sql;     	
	    $result = mysql_query($this->sql,$this->conn); 
    
		if(!$result){
			//调试中使用，sql语句出错时会自动打印出来
			if($this->show_error){
				$this->show_error("错误sql语句：",$this->sql);
				return  $result;
			}
		}else{
			$this->result = $result; 
		}		
    	return $this->result; 	  
	}
	
	//开启事务
	function commit_start(){
		$this->conn->autocommit(false);
	}
	
	//回滚事务
	function commit_rollback(){
		$this->conn->rollback();
	}
	
	//结束事务
	function commit(){
		$this->conn->commit();
	}
	
	function commit_end(){
		$this->conn->autocommit(true);
	}
		
	//事务处理
	function commit_query($sql){		
		if($sql == ""){
			$this->show_error("sql语句错误：","sql查询语句为空");
			return false;
		} 
		try {
//  			$sql =  $this->conn->real_escape_string ($sql);
			$this->sql = $sql;
			$result = $this->conn->query($this->sql);
			
			if(!$result){
				//调试中使用，sql语句出错时会自动打印出来
				if($this->show_error){
					$this->show_error("错误sql语句：",$this->sql);
				}
				return  false;
			}
		} catch (Exception $e) {
			Log::write("commit_query():query() exception :".$e->getMessage(), "log");
			return false;
		}
  	  	
		$this->result = $result;
		return $this->result; 	  
	}
	
	function commit_errno(){
		return $this->conn->errno;
	}
	
	//取得结果集中的关联数组
	public function commit_fetch_assoc_rows()
	{
		$arrRows = array();
		while($row = $this->result->fetch_assoc()){
			array_push($arrRows, $row);
		}
		return $arrRows;
	}
	// 根据select查询结果计算结果集条数
	function commit_db_num_rows(){
		if($this->result==null){
	
			if($this->show_error){
				$this->show_error("sql语句错误","暂时为空，没有任何内容！");
			}
			return -1;
		}
			
		return  $this->result->num_rows;
	}
	
	function commit_fetch_row(){
		if (!$this->result){
			return 0;
		}
		return mysqli_fetch_row($this->result);
	}
	/*创建添加新的数据库*/
	 function create_database($database_name){
		$database=$database_name;
		$sqlDatabase = 'create database '.$database;
		$result = $this->query($sqlDatabase);
		return $result;
	}
	
	/*查询服务器所有数据库*/
	//将系统数据库与用户数据库分开，更直观的显示
	 function show_databases(){
		$result = $this->query("show databases");
		if(!$result){
			return false;			
		}
		
		$amount = $this->db_num_rows($result);
		echo "现有数据库：".$amount ;
		echo "<br />";
		
		$i=1;
		$row = $this->fetch_array();
		if($row ){			
			echo "$i $this->row['Database']";			
			echo "<br />";
			$i++;
		}
		return true;
	}
	
	//以数组形式返回主机中所有数据库名 
	 function databases() 
	{ 
		$rsPtr=mysql_list_dbs($this->conn); 
		$i=0; 
		$cnt=mysql_num_rows($rsPtr); 
		while($i<$cnt) 
		{ 
		  $rs[]=mysql_db_name($rsPtr,$i); 
		  $i++; 
		} 
		return $rs; 
	}
	
	
	/*查询数据库下所有的表*/
	function show_tables($database_name){
		$this->query("show tables");
		echo "现有数据库表：".$amount = $this->db_num_rows();
		echo "<br />";
		$i=1;
		$row = $this->fetch_array();
		while($row){
			$columnName="Tables_in_".$database_name;
			echo "$i $row[$columnName]";
			echo "<br />";
			$i++;
		}
	}
	
	/*
	mysql_fetch_row()    array  $row[0],$row[1],$row[2]
	mysql_fetch_array()  array  $row[0] 或$row[id]
	mysql_fetch_assoc()  array  ��$row->content 字段大小写敏感
	mysql_fetch_object() object ��$row[id],$row[content]字段大小写敏感
	*/
	
	/*取得结果数据*/
	 function mysql_result_li()  
	{ 
		return mysql_result($this->result); 
	} 
	 
	/*取得记录集,获取数组-索引和关联,使用$row['content'] */
	function fetch_array()  
	{		
		return mysql_fetch_array($this->result); 
	}
	
	
	//获取关联数组,使用$row['字段名']
	public function fetch_assoc() 
	{ 
		return mysql_fetch_assoc($this->result); 
	}
	//取得结果集中的关联数组
	public function fetch_assoc_rows()
	{
		$arrRows = array();
		while($row = mysql_fetch_assoc($this->result)){
			array_push($arrRows, $row);
		}
		return $arrRows;
	}
	
	//获取数字索引数组,使用$row[0],$row[1],$row[2]
	 function fetch_row() 
	{ 
		if (!$this->result){
			return 0;
		}
		return mysql_fetch_row($this->result); 
	} 
	
	//获取对象数组,使用$row->content 
	 function fetch_Object() 
	{ 
		return mysql_fetch_object($this->result); 
	}	
	
	function fetch_Object_rows()
	{
		$arr_object = array(); 
		while($row = mysql_fetch_object($this->result)){
			array_push($arr_object, $row);
		}
		return $arr_object;
	}
	
	
	function findall($table)
	{
		$this->query("SELECT * FROM $table");
	}
	
	
	//简化查询select 
	 function select($table,$columnName,$condition)
	{
		if($columnName==""){
			$columnName="*";
		}

		$this->query("SELECT $columnName FROM $table $condition");
	}
	
	//简化删除del
	 function delete($table,$condition){ 
		$this->query("DELETE FROM $table WHERE $condition");
	} 
 
	//简化插入insert
	function insert($table,$columnName,$value){ 
		$this->query("INSERT INTO $table ($columnName) VALUES ($value)");
	} 
 
	//简化修改update
	 function update($table,$mod_content,$condition){ 
		$this->query("UPDATE $table SET $mod_content WHERE $condition");
	}
		
	
	/*取得上一步 INSERT 操作产生的 自动增长的ID*/
	function insert_id(){
		return mysql_insert_id();
    }
		
	//指向确定的一条数据记录
	 function db_data_seek($id){
		if($id>0){
			$id=$id-1;
		}
		if(!@mysql_data_seek($this->result,$id)){
			$this->show_error("sql语句有误：", "指定的数据为空");		
		}
		return $this->result; 
	}
/***
 * 有点问题
 */	
	function fetch_rows_num(){
		if($this->result==null){
	
			if($this->show_error){
				$this->show_error("sql语句错误","暂时为空，没有任何内容！");
			}
			return -1;
		}
		return mysql_fetch_array($this->result, MYSQL_NUM);		
	}
	
	function fetch_rows_assoc(){
		
		if($this->result==null){
	
			if($this->show_error){
				$this->show_error("sql语句错误","暂时为空，没有任何内容！");
			}
			return -1;
		}
		return mysql_fetch_array($this->result, MYSQL_ASSOC);
	}

	// 根据select查询结果计算结果集条数 
	function db_num_rows(){ 
		 if($this->result==null){
			 
		 	if($this->show_error){
		 		$this->show_error("sql语句错误","暂时为空，没有任何内容！");
			}		
			return -1;
		 }
		 
		 return  mysql_num_rows($this->result);
	}
	
	// 根据insert,update,delete执行结果取得影响行数
	function db_affected_rows(){ 
		 return mysql_affected_rows(); 
	}
	
	function getNewId($table, $id){
		$sql = "SELECT max(".$id.") FROM ".$table;
		$result = $this->commit_query($sql);
		if(!$result){
			return false;	
		}
		$row = $this->fetch_array();
		return $row[$id] + 1;
	}
	
	function getLastThemeId(){
		return mysql_insert_id($this->conn);
	}
	
	function show_error($message="",$sql=""){
		if(!$sql){
			Log::write("Error messge:".$message, "log");
		}else{
			Log::write("Error Tip:\n 错误号：12142\n reason:".$this->conn->errno."\n".$message."\n cause:".$this->conn->error."\n SQL:".$sql);
		}	
	}
	
	//输出显示sql语句
	 function show_error_bak($message="",$sql=""){
		if(!$sql){
			echo "<font color='red'>".$message."</font>";
			echo "<br />";
		}else{
			echo "<fieldset>";
			echo "<legend>错误信息提示:</legend><br />";
			echo "<div style='font-size:14px; clear:both; font-family:Verdana, Arial, Helvetica, sans-serif;'>";
			echo "<div style='height:20px; background:#000000; border:1px #000000 solid'>";
			echo "<font color='white'>错误号：12142</font>";
			echo "</div><br />";			
			echo "错误原因：".mysql_error()."<br /><br />";
			echo "<div style='height:20px; background:#FF0000; border:1px #FF0000 solid'>";
			echo "<font color='white'>".$message."</font>";
			echo "</div>";
			echo "<font color='red'><pre>".$sql."</pre></font>";
			$ip=$this->getip();			
			if($this->bulletin){
			$time = date("Y-m-d H:i:s");
					$message=$message."\r\n$this->sql"."\r\n客户IP:$ip"."\r\n时间 :$time"."\r\n\r\n";
				
					$server_date=date("Y-m-d");
					$filename=$server_date.".txt";
					$file_path="error/".$filename;
					$error_content=$message;
					//$error_content="错误的数据库，不可以链接";
					$file = "error"; //设置文件保存目录
					
					//建立文件夹
					if(!file_exists($file)){
						if(!mkdir($file,0777)){
						//默认的 mode 是 0777，意味着最大可能的访问权
							die("upload files directory does not exist and creation failed");
						}
					}
					
					//建立txt日期文件
					if(!file_exists($file_path)){
					
						//echo "建立日期文件";
						fopen($file_path,"w+");
						
						//首先要确定文件存在并且可写
						if (is_writable($file_path))
						{
							//使用添加模式打开$filename，文件指针将会在文件的开头
							if (!$handle = fopen($file_path, 'a')) 
							{
								echo "不能打开文件 $filename";
								exit;
							}
					
								//将$somecontent写入到我们打开的文件中。
							if (!fwrite($handle, $error_content)) 
							{
								echo "不能写入到文件 $filename";
								exit;
							}
					
							//echo "文件 $filename 写入成功";
							
							echo "——错误记录被保存!";
							
					
							//关闭文件
							fclose($handle);
						} else {
							echo "文件 $filename 不可写";
						}
						
					}else{
						//首先要确定文件存在并且可写
						if (is_writable($file_path))
						{
							//使用添加模式打开$filename，文件指针将会在文件的开头
							if (!$handle = fopen($file_path, 'a')) 
							{
								echo "不能打开文件 $filename";
								exit;
							}
					
								//将$somecontent写入到我们打开的文件中。
							if (!fwrite($handle, $error_content)) 
							{
								echo "不能写入到文件 $filename";
								exit;
							}
					
							//echo "文件 $filename 写入成功";
							echo "——错误记录被保存!";
							
							//�ر��ļ�
							fclose($handle);
						} else {
							echo "文件 $filename 不可写";
						}
					}
				
				}
				echo "<br />";
				if($this->is_error){
					exit;
				}
			}
			echo "</div>";
			echo "</fieldset>";
		echo "<br />";
	}
	
	
	//释放结果集 
	function free(){ 
		try{
			if($this->conn_type != "commit"){
				@mysql_free_result($this->result);
			}else{
				$this->result->close();
			}	
		}catch(Exception $e){
			Log::write("mySql::free() failed excption: ".$e->getMessage(), "log");
		}			 
	}
	
	//数据库选择
	 function select_db($db_database){ 
		return mysql_select_db($db_database);
	}
	
	//查询字段数量
	function num_fields($table_name){ 
		//return mysql_num_fields($this->result);
		$this->query("select * from $table_name");
		if(!$this->result){
			return false;
		}
		echo "<br />";
		echo "字段数：".$total = mysql_num_fields($this->result);
		echo "<pre>";
		for ($i=0; $i<$total; $i++){
			print_r(mysql_fetch_field($this->result,$i) );
		}
		echo "</pre>";
		echo "<br />";
	}
	
	//取得 MySQL 服务器信息
	function mysql_server($num=''){
		switch ($num){
			case 1 :
			return mysql_get_server_info(); //MySQL 服务器信息	
			break;
			
			case 2 :
			return mysql_get_host_info();   //取得 MySQL 主机信息
			break;
			
			case 3 :
			return mysql_get_client_info(); //取得 MySQL 客户端信息
			break;
			
			case 4 :
			return mysql_get_proto_info();  //取得 MySQL 协议信息
			break;
			
			default:
			return mysql_get_client_info(); //默认取得mysql版本信息
		}
	}
	
	//析构函数，自动关闭数据库,垃圾回收机制
	 function __destruct()
	{
		
		if(!empty($this->result) && !is_bool($this->result)){ 
			$this->free();
		}
		//echo "对象被释放";
		if($this->conn_type != "commit")	{	
			mysql_close($this->conn);
		}else 
		{
			$this->conn->close();
		}
	}//function __destruct();

	/*获得客户端真实的IP地址*/
	function getip(){ 
		if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		{
			$ip = getenv("HTTP_CLIENT_IP"); 
		}
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
			$ip = getenv("HTTP_X_FORWARDED_FOR"); 
		}
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		{
			$ip = getenv("REMOTE_ADDR"); 
		}
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
		$ip = $_SERVER['REMOTE_ADDR']; 
		}
		else{
			$ip = "unknown"; 		
		}
		return($ip);
	}	
}
?>
