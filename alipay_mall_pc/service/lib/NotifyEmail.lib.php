<?php
require_once 'public/public.php';
require_once 'configs/config.php';
require_once 'Zend/Mail.php';
require_once 'Zend/Mail/Transport/Smtp.php';
require_once 'lib/WriteLog.lib.php';

class UserInfo {
	public $name;		//名字
	public $email;		//EMAIL
	public $notify;		//0 ：收件人， 1： 抄送人， 2：暗送人
	
	function __construct($name = '', $mail = '', $notify = ''){
		$this->name   = $name;
		$this->email  = $mail;
		$this->notify = $notify;
	}	
	
	function  setEmail($email){
		$this->email = $email;
	}
	function getEmail(){
		return $this->email;
	}
	function setName($name){
		$this->name = $name;	
	}
	function getName(){
		return $this->name;		
	}
	function setNotify($notify){
		$this->notify = (int)$notify;
	}
	function getNotify(){
		return $this->notify;
	}
	function setUserByDB($row){
		$this->name		= $row['name'];
		$this->email	= $row['email'];
		$this->notify	= 0;
	}
}

class NotifyEmail{
	var $arr_user = array();
	var $subject;
	var $msg_body;
	var $transport;
	var $mail;	
	
	function __construct(){
		global $g_arr_email;
		global $g_arr_email_config;
		$this->transport = new Zend_Mail_Transport_Smtp($g_arr_email['smtp'], $g_arr_email_config);
		$this->subject = "BUG Report";
	}	

	function pushUser($user){
		array_push($this->arr_user, $user);
	}
	
	function setSubject($subject){
		$this->subject = $subject;
	}
	
	function getSubject(){
		return $this->subject;
	}
	function setMsgBody($body){
		$this->msg_body = $body;
	}
	function getMsgBody(){
		return $this->msg_body;
	}
	
	function setUsers($arr_user){
		$this->arr_user = $arr_user;
	}
	function getUsers(){
		return $this->arr_user;
	}
	
	/**
	 * 此处通知内容必须为HTML格式的内容，表格或者表单
	 */
	function notify(){
		
		if (count($this->arr_user) <= 0){
			Log::write("EmailNotify::notify() arr_user is empty", "log");
			return false;
		}
		
		$result = $this->sendEmail($this->subject, $this->msg_body, $this->arr_user);
		if(!$result){
			Log::write("NotifyEmail::notify() faild", "log");
		}
		return $result;
	}
	
	/**
	 * 邮件通知函数，通知列表为用户数组
	 * @param unknown_type $subject
	 * @param unknown_type $msg_body
	 * @param unknown_type $arr_to_user
	 * @return boolean
	 */
	function sendEmail($subject, $msg_body, $arr_to_user, $arr_attachment=null){
		try{
			$this->mail = new Zend_Mail();
			foreach ($arr_to_user as $to_user ){
				switch ($to_user->notify){
					case 0:{
						$this->mail->addTo($to_user->getEmail(), 	$to_user->getName());	//增加一个收件人到邮件头“To”（收件人）
					}break;
					case 1:{
						$this->mail->addCc($to_user->getEmail(), 	$to_user->getName());	//增加一个收件人到邮件头“Cc”（抄送）
					}break;
					case 2:{
						$this->mail->addBcc($to_user->getEmail(), 	$to_user->getName()); //增加一个收件人到邮件头“Bcc”（暗送）
					}break;
					default:{
						$this->mail->addTo($to_user->getEmail(), 	$to_user->getName());	
					}break;
				}			
			}
			
			global $g_arr_email;			
			$this->mail->setFrom($g_arr_email['from'], $g_arr_email['sender']);
			
			$this->mail->setSubject("=?UTF-8?B?".base64_encode($subject)."?=");
			$this->mail->setBodyHtml($msg_body, 'utf-8', Zend_Mime::ENCODING_BASE64);
			if($arr_attachment != null){
				foreach ($arr_attachment as $attachment){
					foreach ($attachment as $key=>$value){
						if(!file_exists($value)){
							continue;
						}
 						$attache =  $this->mail->createAttachment(file_get_contents($value));
// 						$attache->disposition = Zend_Mime::DISPOSITION_INLINE;
 						$attache->filename 	  = $key;
// 						$at = new Zend_Mime_Part(file_get_contents($value));
//  						$at->type        = 'image/png'; 					//: 'application/octet-stream';
// 						$at->disposition = Zend_Mime::DISPOSITION_INLINE;	//:'inline'
// 						$at->encoding    = Zend_Mime::ENCODING_BASE64;
// 						$at->filename    = $key;
// 						$this->mail->addAttachment($at);
					}
				}		
			}
			$this->mail->send($this->transport);			
		}catch (Zend_Exception $e){
			Log::write("NotifyEmail::sendEmail() zend_exception : ".$e->getMessage(), "log");
			return false;
		}
		return true;
	}
}