<?php
/**
 * 文件上传实现类
 * 
 * @author lijie1@yulong.com
 */

require_once ("lib/WriteLog.lib.php");

class FileUpload{
		
	private  $f_size_max = 20481000;  		//允许上传的文件大小
	private  $overwrite = 0;				//是否允许覆盖,1:允许,0:不允许
	private  $f_type = "swf,jpg,rar,zip,7z,iso,gif";	//上传文件类型
	private  $s_des_dir = './file/';
	
	function __construct() {
		$this->f_size_max = 20481000;  		
		$this->overwrite = false;			
		$this->f_type = "swf,jpg,rar,zip,7z,iso,gif";
		$this->s_des_dir = './file/';
	}
	
	function FileUpload($f_size_max, $f_type, $overwrite, $s_des){
		$this->f_size_max 	 = $f_size_max;
		$this->f_type 		 = strtolower($f_type);
		$this->overwrite 	 = $overwrite;
		$this->s_des_dir 	 = $s_des;
	}
	
	public function initData($f_size_max, $f_type, $overwrite, $s_des){
		$this->f_size_max 	 = $f_size_max;
		$this->f_type 		 = strtolower($f_type);
		$this->overwrite 	 = $overwrite;
		$this->s_des_dir 	 = $s_des;
	} 
	
	public function setDesDir($s_des){
		$this->s_des_dir = $s_des;
	}
	
	public function setMaxSize($f_size){
		$this->f_size_max = $f_size;
	}
	
	public function getExt(){
		return $this->f_type;
	}
	
	public function getSize(){
		return $this->f_size_max;
	}
	
	public function moveFile($f_src, $f_des){
		try {
			//Log::write("FileUpload::MoveFile():".$f_src." to ".$f_des, "log");
			if(!move_uploaded_file($f_src, $f_des)){
				return false;
			}
		} catch (Exception $e) {
			
			return  false;
		}	
		return true;
	}
	
	public function checkExt($f_name){
			
		$tmp_type = substr(strrchr($f_name,"."),1);//��ȡ�ļ���չ��
		$tmp_type = strtolower($tmp_type);
			
		if(!stristr($this->f_type, $tmp_type)){
			return false;
		}
		return true;
	}
	
	public function checkSize($f_size){
	
		if ($f_size > $this->f_size_max) {
				
			return false;
		}
		return true;
	}
	
	public function checkExist($f_des){
		if (file_exists($f_des)&& ! $this->overwrite){
	
			return  false;
		}
		return true;
	}
	
	public function makeDesDir(){
		$dir_dest = iconv('utf-8', 'gb2312', $this->s_des_dir);			
		if(!is_dir($dir_dest)){
			$result = mkdir($dir_dest, 0700, true);
			if (!$result) {
				Log::write("FileUpload::MakeDesDir():".$this->s_des_dir." failed", "log");
				return false;
			}
		}
		return true;
	}
	
	public function getUploadfile($f_type){
		$string = 'abcdefghijklmnopgrstuvwxyz0123456789';
		$rand = '';
		for ($x=0; $x < 12; $x++){
			$rand .= substr($string, mt_rand(0,strlen($string)-1),1);
		}
		
		$t = date("ymdHis").substr($gettime[0], 2, 6).$rand;
		$uploadfile = $this->s_des_dir.$t.".".$f_type;
		
		//Log::write("FileUpload::GetUploadfile():".$uploadfile, "log");
		return $uploadfile;
	}
	
	public function uploadFile($error, $f_name, $f_tmp, $f_size){
		
		$up_error="no";
		if ($error == UPLOAD_ERR_OK){

			$uploadfile = strtolower(basename($f_name));
			$f_type = substr(strrchr($f_name,"."),1);//��ȡ�ļ���չ��
			$f_type = strtolower($f_type);
			
			if(!$this->checkExt($f_name)){
				echo "<script>alert('�Բ���,ֻ���ϴ�".$this->GetExt()."�ļ��ϴ�ʧ��!')</script>";
				$up_error="yes";
			}
		
			if (!$this->checkSize($f_size)) {
				echo "<script>alert('�Բ���,���ϴ����ļ� ".$f_name." ����Ϊ".round($f_size/1024)."Kb,���ڹ涨��".($this->GetSize()/1024)."Kb,�ϴ�ʧ��!')</script>";
				$up_error="yes";
			}
		
			if (!$this->checkExist($uploadfile) && !$overwrite){
				echo "<script>alert('�Բ���,�ļ� ".$f_name." �Ѿ�����,�ϴ�ʧ��!')</script>";
				$up_error="yes";
			}
		
			if(!$this->makeDesDir()){
				echo "<script>alert('�Բ���,�����ϴ�·��shiba,�ϴ�ʧ��!')</script>";
				$up_error="yes";
			}
			
			if($up_error!="yes"){
				
				$f_des = $this->getUploadFile($f_type);
				$f_src = $f_tmp;
 				
				if($this->moveFile($f_src, $f_des)){
					$_msg=$_msg.$f_name.'�ϴ��ɹ�\n';
				}else{
					$_msg=$_msg.$f_name.'�ϴ�ʧ��\n';
				}
			}
		}
		return $_msg;
	}
}
?>