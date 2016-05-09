<?php
class Fnlib
{
	const UPLOAD_ERR_OK 		= 1;
	const UPLOAD_ERR_FILE_NAME	= 2;
	const UPLOAD_ERR_FILE_SIZE 	= 3;
	const UPLOAD_ERR_FILE_TMP 	= 4;
	
	
	public static function remove_file_path($strDir){
		$handle = opendir($strDir);
		if($handle === false){
			return false;
		}
	
		while(false !== ($item = readdir($handle))){
			if($item != "." && $item != ".."){
				if(is_dir("$strDir/$item")){
					self::remove_file_path("$strDir/$item");
				}else{
					if(!unlink("$strDir/$item")){
						return false;
					}
				}
			}
		}
		closedir($handle);
	
		if(!rmdir($strDir)){
			return false;
		}
		return true;
	}
	
	public static function get_zip_entry_content2($zip_file, $entry)
	{
		$fp = fopen('zip://'.$zip_file.'#'.$entry, 'r');
		if (!$fp) {
			Yii::log('fnlib::get_zip_entry_content1()zip_file:'.$zip_file.' failed', 'log');
			return false;
		}
	
		while (!feof($fp)) {
			$contents .= fread($fp, 2);
		}
		fclose($fp);
		return $contents;
	}
	
	public static function get_zip_entry_content($zip_file, $entry)
	{
		try{
			$zip = zip_open($zip_file);
			Yii::log('fnlib::get_zip_entry_content()zip_file:'.$zip_file, 'log');
				
			$b_find = false;
			if (!is_resource($zip)){
				Yii::log('fnlib::get_zip_entry_content()zip:'.$zip.'  is_resource failed', 'log');
			}
			while ($zip_entry = zip_read($zip)) {
				$file_name = zip_entry_name($zip_entry);
				if(substr($file_name, 0, strlen($entry))!= $entry){
					continue;
				}
				$b_find = true;
				if (!zip_entry_open($zip, $zip_entry, "r")){
					continue;
				}
				$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
				zip_entry_close($zip_entry);
				break;
			}
			// 			}
			zip_close($zip);
		}catch(Exception $e){
			Yii::log('fnlib::get_zip_entry_content()exception error:'.$e->getMessage(), 'log');
			return false;
		}
		if($b_find){
			return $content;
		}
		Yii::log('fnlib::get_zip_entry_content() b_find is false', 'log');
		return false;
	}
	
	public static function remove_path_file($strDir){
		if(!file_exists($strDir)){
			return true;
		}
		$handle = opendir($strDir);
		if($handle === false){
			return false;
		}
	
		while(false !== ($item = readdir($handle))){
			if($item != "." && $item != ".."){
				if(is_dir("$strDir/$item")){
					self::remove_file_path("$strDir/$item");
				}else{
					if(!unlink("$strDir/$item")){
						return false;
					}
				}
			}
		}
		closedir($handle);
		return true;
	}
	
	public static function get_rsp_result($value, $errCode = '',$msg=''){
		$result = array('result'=>$value,
				        'error'=>$errCode,
						'msg'  =>$msg
		);
		return json_encode($result);
	}
	
	public static function sql_check_str($str)
	{
		$refuse_str="and|or|select|update|from|where|order|by|*|delete|'|insert|into|values|create|table|database";
		$arr=explode("|",$refuse_str);
		$tmpLen = count($arr);
		for($i=0;$i<$tmpLen;$i++)
		{
			$replace="[".$arr[$i]."]";
			$str=str_replace($arr[$i],$replace,$str);
		}
		return $str;
	}
	
	public static function makeDesDir($strDir)
	{
		$dir_dest = iconv('utf-8', 'gb2312', $strDir);
		if(!is_dir($dir_dest)){
			$result = mkdir($dir_dest, 0700, true);
			if (!$result) {
				Yii::log("fnlib::makeDesDir():".$dir_dest." failed", "info");
				return false;
			}
		}
		return true;
	}
	
	public static function  get_file_info($input, $key, &$f_name, &$f_size, &$f_tmp_file){
		if ($key < 0){
			$f_name 	= $_FILES[$input]['name'];
			$f_size 	= $_FILES[$input]['size'];
			$f_tmp_file = $_FILES[$input]['tmp_name'];
		}else{
			$f_name 	= $_FILES[$input]['name'][$key];
			$f_size 	= $_FILES[$input]['size'][$key];
			$f_tmp_file = $_FILES[$input]['tmp_name'][$key];
		}
	
		$result = self::UPLOAD_ERR_OK;
		if($f_name == null){
			Yii::log("f_name is empty", "info");
			$result = self::UPLOAD_ERR_FILE_NAME;
			return $result;		//返回错误代码 107:主题文件名为空
		}
	
		if($f_size == null){
			Yii::log("f_size is empty", "info");
			$result = self::UPLOAD_ERR_FILE_SIZE;
			return $result;	//返回错误代码 108:文件大小为空
		}
	
		if($f_tmp_file == null){
			Yii::log("f_tmp_file is empty", "info");
			$result = self::UPLOAD_ERR_FILE_TMP;
			return $result;		//返回错误代码 109:文件上传缓存失败
		}
		return $result;
	}
	
	static function object_to_array($object){
			
		$result = array();
			
		$object = is_object($object) ? get_object_vars($object) : $object;
			
		foreach ($object as $key => $val) {
	
			$val = (is_object($val) || is_array($val)) ? object_to_array($val) : $val;
	
			$result[$key] = $val;
		}
			
		return $result;
	}
	
	
	static function get_respond_by_url($url, $date, $methord = 0){
		try{
			$ch = curl_init();
	
			if(!empty($date)){
				$url = $url.'?'.$date;
			}
	
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
			$curl_result = curl_exec($ch);
	
			curl_close($ch);
		}catch(Exception $e){
			Yii::log("fnlib::get_respond_by_url() exception error:".$e->getMessage(), "info");
			return false;
		}
		return $curl_result;
	}
	
	static function post_respond_by_url($url, $data, $methord = false){
		try{
			$ch = curl_init();
				
			if(!empty($data)){
				$url = $url.'?'.$data;
			}
	
			$bResult = curl_setopt($ch, CURLOPT_URL, $url);
			if(!$bResult){
				Yii::log('fnlib::get_respond_by_url():curl_setopt(CURLOPT_URL) failed', 'log');
				return false;
			}
				
			if($methord){
				$bResult = curl_setopt($ch, CURLOPT_POST, 1);
				if(!$bResult){
					Yii::log('fnlib::get_respond_by_url():curl_setopt(CURLOPT_POST) failed', 'log');
					return false;
				}
			}
				
			$bResult = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(!$bResult){
				Yii::log('fnlib::get_respond_by_url():curl_setopt(CURLOPT_RETURNTRANSFER) failed', 'log');
				return false;
			}
	
			$curl_result = curl_exec($ch);
			if($curl_result === false){
				Yii::log('fnlib::get_respond_by_url():curl_exec failed', 'log');
				return false;
			}
			curl_close($ch);
		}catch(Exception $e){
			Yii::log("fnlib::get_respond_by_url() exception error:".$e->getMessage(), "info");
			return false;
		}
// 		Yii::log("fnlib::get_respond_by_url() :".$curl_result, "info");
		return $curl_result;
	}
	
	static function create_guid()
	{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$uuid   = substr($charid, 0, 16);
		return $uuid;
	}
	
	static function declassify($str)
	{
		$iv_size 	= 16;
		$privateKey = "token_key";//约定的秘钥
		$cryptkey 	= hash('md5',$privateKey);
		$url_dec	=base64_decode($str);
		$url_iv 	= substr($url_dec,0,$iv_size);
		$url_mis 	= substr($url_dec,$iv_size);
		$url_exp    = openssl_decrypt($url_mis,'aes-256-cbc',$cryptkey,false,$url_iv);
		return $url_exp;
	}
	
	
	static function enclassify($str)
	{
		$privateKey = "token_key";//约定的秘钥
		$cryptkey 	= hash('md5',$privateKey);
		$data 		= $str;
		$iv_size 	= 16;
		$iv 		= self::create_guid();
		$enc 		= openssl_encrypt($data,'aes-256-cbc',$cryptkey,false,$iv);
		$url_enc	= base64_encode($iv.$enc);
		return $url_enc;
	}
	
	static function geturldatapost( $url,$post_data )
	{
		$ch     = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		ob_start();
		$result = curl_exec($ch);
		$result = ob_get_contents() ;
		ob_end_clean();
		return $result;
	}
	
	static function jumppost( $url,$post_data )
	{
		$ch     = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_exec($ch);
	}

	
	/**
	 * 根据URL跳转下载，要求path为文件的完整URL
	 * @param unknown_type $path
	 */
	static function url_skip_download($path){
		try{
			$pos = strrpos($path, '/');
			$name = substr($path, $pos + 1,  strlen($path) - $pos -1);
			$name = urlencode($name);
	
			$name = str_replace("+", "%20", $name);
			$path_tmp = substr($path, 0, $pos + 1);
	
			header('content-type: application/file');
			header('content-disposition: attachment; filename='.$name);
			header('location: '.$path_tmp.$name);
	
			return true;
		}catch(Exception $e){
			Yii::log("fnlib::url_skip_download() exception error:".$e->getMessage(), "info");
			return false;
		}
		return false;
	}
	
	static function record_time_cost($strLogs, $tstart)
	{
		$tend = fnlib::microtime_float();
		//		Yii::log($strLogs.($tend - $tstart).' ms', "debug");
	}
	
	static function microtime_float()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}