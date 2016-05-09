<?php
	require_once 'configs/config.php';
	require_once 'lib/fileUpload.lib.php';
	
	define("UPLOAD_FILE_ERR_LOGIN", 		1001);
	define("UPLOAD_FILE_ERR_NAME", 			1002);
	define("UPLOAD_FILE_ERR_FILE_INFO", 	1003);
	define("UPLOAD_FILE_ERR_FILE_UPLOAD", 	1004);
	define("UPLOAD_FILE_ERR_DB", 			1005);
	define("UPLOAD_FILE_ERR_DB_CONN", 		1006);
	define("UPLOAD_FILE_ERR_EXCPTION", 		1008);
	
	define('UPLOAD_ERR_Ext', 101);
	define('UPLOAD_ERR_MOVE', 102);
	define('UPLOAD_ERR_EXIST', 103);
	define('UPLOAD_ERR_SIZE', 104);
	define('UPLOAD_ERR_IMG_RESIZE', 105);
	define('UPLOAD_ERR_IMG_EXCP', 106);
	
	define('UPLOAD_ERR_FILE_NAME', 107);
	define('UPLOAD_ERR_FILE_SIZE', 108);
	define('UPLOAD_ERR_FILE_TMP', 109);
	class CollecterFile{
		var $f_md5;					//上传文件MD5
		var $f_des_folder;			//目标文件夹名称
		var $f_des_dir;				//目标路径
		var $f_des_file;			//目标文件URI
		var $f_url;					//文件相对URL
		var $f_size_max;			//上传文件大小最大值 
		var $f_type;				//上传文件类型
		var $overwrite;
		var $file_upload;
			
		function __construct(){
			$this->f_md5 = "";
			$this->f_des_folder = "";
			$this->f_des_dir  = "";
			$this->f_des_file = "";
			$this->f_url = "";
			$this->file_dir = "";
			$this->f_size_max = 0;
			$this->f_type = 0;
			$this->overwrite = false;
			$this->file_upload = new FileUpload();
		}
		
		function getFile(&$folder, &$url, &$md5){
			$folder = $this->f_des_folder;
			$url 	= $this->f_url;
			$md5 	= $this->f_md5;	
		}
			
		/**
		 * @param unknown_type $error  文件上传错误信息
		 * @param unknown_type $f_name 文件名
		 * @param unknown_type $f_size 文件大小
		 * @param unknown_type $f_tmp_file 文件缓存
		 */
		function fileUpload($error, 
							$f_name, 
							$f_size, 
							$f_tmp_file) {

			if(!$this->file_upload){
				Log::write("CollecterFile::fileUpload(f_theme_max_size) failed", "log");
				return false;
			}
						
			$this->file_upload->initData($this->f_size_max, 
										$this->f_type, 
										$this->overwrite, 
										$this->f_des_dir);
			
			$result = $this->uploadFile($error, $f_name, $f_tmp_file, $f_size, $this->f_des_file);
			if($result != UPLOAD_ERR_OK){
				Log::write("CollecterFile::fileUpload():uploadFile() failed ERRO NO: ".$result, "log");
				return $result;
			}			
			return $result;
		}
		
		/**
		 * 文件上传
		 * @param unknown_type $error 	上传错误信息
		 * @param unknown_type $f_name	文件名
		 * @param unknown_type $f_tmp	缓存文件
		 * @param unknown_type $f_size	文件大小
		 * @param unknown_type $f_des_file	目标文件
		 * @return error no					返回错误代码
		 */
		function uploadFile($error, $f_name, $f_tmp, $f_size, $f_des_file) {
			$err_no = UPLOAD_ERR_OK;
			$up_error="no";
		
			if ($error == UPLOAD_ERR_OK){
				$f_type = substr(strrchr($f_name,"."),1);//获取文件扩展名
				$f_type = strtolower($f_type);
		
				if(!$this->file_upload->checkExt($f_name)){
					$err_no = UPLOAD_ERR_Ext;
					$up_error="yes";
				}
		
				if (!$this->file_upload->checkSize($f_size)) {
					$err_no = UPLOAD_ERR_SIZE;
					$up_error="yes";
				}
		
				$uploadfile = strtolower(basename($f_name));
				if (!$this->file_upload->checkExist($uploadfile) && !$this->overwrite){
					$err_no = UPLOAD_ERR_EXIST;
					$up_error="yes";
				}
		
				if(!$this->file_upload->makeDesDir()){
					$err_no = UPLOAD_ERR_MOVE;
					$up_error="yes";
				}
		
				if($up_error!="yes"){
					if(is_uploaded_file($f_tmp)){
						$file_dest = iconv('utf-8', 'gb2312', $f_des_file);
						if(!$this->file_upload->moveFile($f_tmp, $file_dest)){
							$err_no = UPLOAD_ERR_MOVE;
						}
					}
				}
			}
			return $err_no;
		}

		/**
		 * 设置文件基本属性：MD5\URL\文件夹
		 * @param unknown_type $xiu_type
		 * @param unknown_type $f_tmp_file
		 * @param unknown_type $f_name
		 */
		function setFileParam($arr_file_config, $f_tmp_file, $f_name, $folder){
			$this->file_dir 	= $arr_file_config['dir'];
			$this->f_size_max 	= $arr_file_config['max_size'];
			$this->f_type 		= $arr_file_config['file_type'];

			$this->overwrite 	= true;
			
			$this->f_md5 		= md5_file($f_tmp_file);
			$this->f_des_folder = $folder;
			$this->f_des_dir 	= $folder;//$this->getDesDir($this->file_dir, $this->f_des_folder);				
			$this->f_des_file 	= $this->getDesFile($this->f_des_dir, $f_name);
			$this->f_url 		= $this->getUrl($this->f_des_file);			
		}
		
		/**
		 * 根据上传文件类型获取相关配置信息
		 * 
		 * @param unknown_type $xiu_type 		文件类型 ： 0 ：主题 1：主题预览 2：壁纸
		 * @param unknown_type $file_dir		文件的根目录
		 * @param unknown_type $f_size_max		约定的文件大小最大值
		 * @param unknown_type $f_type  		约定的文件类型
		 * @param unknown_type $overwrite		是否可以覆盖
		 */
		function getFileConfig(&$file_dir, &$f_size_max, &$f_type, &$overwrite){
			global $g_arr_file;
			$file_dir 		= $g_arr_file['dir'];
			$f_size_max 	= $g_arr_file['max_size'];
			$f_type 		= $g_arr_file['file_type'];
		}
		
		/**
		 * 获取存放文件的文件夹名称
		 * 获取字段生成的ID有点麻烦，文件夹是用CRC+时间+随机数生成的
		 */
		function getFolder($crc){
			$str = 'abcdefghijklmnopgrstuvwxyz0123456789';
			$rand = '';
			for ($x=0; $x < 6; $x++){
				$rand .= substr($str, mt_rand(0,strlen($str)-1),1);
			}
		
			$month = date("Ym");
			$t = date("ymdHis")."_".$rand;
			return $month.'/'.$crc."_".$t;
		}
		
		/**
		 * 获取目标目录,根据名称_时间_随机字符生成
		 * $theme_name：主题名称
		 */
		function getDesDir($file_dir, $f_folder){
			$f_des_dir = $file_dir.$f_folder."/";
			return $f_des_dir;
		}
		/**
		 * 获取目标文件URI
		 * $file_name:上传文件名
		 */
		function getDesFile($f_des_dir, $f_name){
			$f_des_file = $f_des_dir.$f_name;
			//Log::write("file name:".$this->f_des_file, "log");
			return  $f_des_file;
		}		
		
		/**
		 * 获取目标文件URL的相对路径
		 * $f_des_file:上传文件目标路径
		 */
		function getUrl($f_des_file){
			$f_url = substr($f_des_file, 2, strlen($f_des_file));
			//Log::write("file url is:".$f_url, "log");
			return $f_url;
		}
	}	
?>