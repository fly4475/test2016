<?php
	require_once 'WriteLog.lib.php';
	class Zip{
		private $_zip;
		
		function __construct(){
			$this->_zip =  new ZipArchive();			
		}
		function deletOldZip($file){
			try{
				if (file_exists($file)){
					$result = unlink($file);
					if(!$result){
						Log::write("fnzip():unlink(".$file.")  failed", "log");
						return false;
					}
				}
			}catch(Exception $e){
				Log::write("deletOldZip() exception, error:".$e->getMessage(), "log");
				return false;
			}
			return true;
		}
		
		/**
		 * 扫描路径压缩 
		 * @param unknown_type $s_dir		源路径
		 * @param unknown_type $file_zip	目标路径
		 * @return boolean
		 */
		function  scanZipDir($s_dir, $file_zip){
			$result = $this->deletOldZip($file_zip);
			if(!$result){
				Log::write("Zip::scanZipDir():deletOldZip()", "log");
				return false;
			}
		
			if ($this->_zip->open($file_zip, ZIPARCHIVE::CREATE)!==TRUE) {
				Log::write("Zip::scanZipDir():open()  failed, cannot open <". $file_zip, "log");
				return false;
			}
		
			$result = $this->zipDir($s_dir, "");
			if(!$result){
				Log::write("Zip::scanZipDir():zipDir()  failed", "log");
				$this->_zip->close();
				return false;
			}
			$zip->close();
		}
		
		/**
		 * 递归路径，并压缩  
		 * @param unknown_type $s_dir     源路径
		 * @param unknown_type $zip_dir   压缩目录
		 * @param unknown_type $zip       目标路径
		 * @return boolean
		 */	
		function zipDir($s_dir, $zip_dir){
			try{
				$handle = opendir($s_dir);
				if (!$handle){
					return false;
				}
		
				while (false !== ($file = readdir($handle))) {
					if ($file == "." || $file == "..") {
						continue;
					}
						
					if(is_dir($s_dir.$file)){
						$result = $this->_zip->addEmptyDir($zip_dir.$file."/");
						if(!$result){
							Log::write("Zip::zipDir():addEmptyDir() failed", "log");
							$this->_zip->close();
							closedir($handle);
							return false;
						}
							
						$result = $this->zipDir($s_dir.$file."/", $zip_dir.$file."/", $this->_zip);
						if(!$result){
							Log::write("Zip::zipDir():zipDir() failed", "log");
							$this->_zip->close();
							closedir($handle);
							return false;
						}
					}
						
					if(is_file($s_dir.$file)){
						$result = $this->_zip->addFile($s_dir.$file, $zip_dir.$file);
						if(!$result){
							Log::write("Zip::zipDir():addFile() failed", "log");
							$this->_zip->close();
							closedir($handle);
							return false;
						}
					}
				}
				closedir($handle);
			}catch (Exception $e){
				Log::write("Zip::zipDir() exception, error:".$e->getMessage(), "log");
				if($handle){
					closedir($handle);
				}
				return false;
			}
			return true;
		}
		
		function uncompressZip($zip_file, $dist){
			try{
				if(!file_exists($zip_file)){
					Log::write("Zip::uncompressZip():file_exists() failed", "log");
					return false;
				}
				
				if($this->_zip->open($zip_file) !== true){
					Log::write("Zip::uncompressZip() open failed", "log");
					return false;
				}
				
				$result = $this->_zip->extractTo($dist);
				if(!result){
					Log::write("Zip::uncompressZip():extractTo() failed", "log");
					$this->_zip->close();
					return false;
				}
				$this->_zip->close();
				
			}catch(Exception $e){
				Log::write("Zip::uncompressZip() exception, error:".$e->getMessage(), "log");
				return false;
			}	
		}
		
		function getEntryContents($file_zip, $entry){
			try{
				if(!file_exists($file_zip)){
					Log::write("Zip::getEntryContents() zip:".$file_zip." not exist", "log");
					return false;
				}
				
				$res = $this->_zip->open($file_zip);
				if ($res !== TRUE) {
					Log::write("Zip::getEntryContents() open zip:".$file_zip."failed error no:".$res, "log");
					return false;
				}
				
				$fp = $this->_zip->getStream($entry);
				if(!$fp){
					Log::write("Zip::getEntryContents():getStream() failed", "log");
					$this->_zip->close();
					return false;
				}
				$contents = "";
				while (!feof($fp)) {
					$contents .= fread($fp, filesize($file_zip));
				}
				fclose($fp);
				$this->_zip->close();
			}catch(Excption $e){
				if(!feof($fp) && !$fp){
					fclose($fp);
				}
				if($res === true){
					$this->_zip->close();
				}
				Log::write("Zip::getEntryContents() exception error:".$e->getMessage(), "log");
				return false;
			}
			return $contents;
		} 
	}

	function fnUnitTest(){
		$dirZip = new DirZip();
		$dirZip->scanZipDir("home/CT01/", "zip/CT01.zip");
	}	
	
	function zipArchiveUnitTest(){
		$zip = new ZipArchive;
		$res = $zip->open('../DumpFiles/zipdump/log.zip');
		if ($res === TRUE) {
		       // echo $zip->getFromIndex(0); //根据索引获取文件内容
		       // echo $zip->getNameIndex(0); //根据索引获取文件名
		       echo $zip->getCommentIndex(0);
		      // echo zip_entry_filesize($res);
/*				$fp = $zip->getStream('logcat.txt');
			    if(!$fp){
			    	echo "getstream failed";
			    	return;
			    }
			    while (!feof($fp)) {
			        $contents .= fread($fp, filesize('../DumpFiles/zipdump/log.zip'));
			    }
				echo $contents;
			    fclose($fp);*/
		} else {
		    echo 'failed, code:' . $res;
		}
	}
	zipArchiveUnitTest();
?>