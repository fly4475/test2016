<?php
class CalloutController extends Controller
{
	/**
	 * 拉取第三方语音外呼
	 */	
	public function actionCall()
	{
		$body      	     = @file_get_contents('php://input');
		if($body == "") exit(json_encode(array("result"=>-1,"msg"=>"param empty")));
		Yii::log('CalloutController::actionCall resource body data is :'.$body,'info');
		
		$bodyArr 		 = json_decode($body,true);
		if(!$bodyArr){
			Yii::log('CalloutController::actionCall body data is fail, data is:'.$body,'info');
			exit(json_encode(array("result"=>-1,"msg"=>"json parse error")));
		}
		
		$phone      = isset($bodyArr['phone'])?$bodyArr['phone']:'';
		$ctrl_id	= isset($bodyArr['ctrl_id'])?$bodyArr['ctrl_id']:'';
		$address    = isset($bodyArr['address'])?$bodyArr['address']:'';
		$user_name  = isset($bodyArr['user_name'])?$bodyArr['user_name']:'';
		$warn_id    = isset($bodyArr['warn_id'])?$bodyArr['warn_id']:'';
		$warn_time  = isset($bodyArr['warn_time'])?$bodyArr['warn_time']:'';
		$room_name  = isset($bodyArr['room_name'])?$bodyArr['room_name']:'';
		
		if($phone == '' || (int)$warn_id <1 || (int)$warn_id >6){
			exit(json_encode(array("result"=>-1,"msg"=>"param empty or error")));
		}
		$message_arr = array("有害气体监控告警","火警监控告警","漏水监控告警","闯入监控告警","可燃气体监控告警","SOS监控告警");
		/**
		 * 合成语音message
		 */
		$message = $room_name.'触发'.$message_arr[(int)$warn_id-1];
// 		$message  = "客厅触发水警监控告警";
		$data = array(
				'tel' 		=> $phone,
				'userField' => '',
				'message'   => $message
		);		
		$callout = new CallModule();
 		$result  = $callout->call($data);
// 		$result = '{"result":"0","uniqueId":"6432f8ef48784b3f5b33977a307215ec","clid":"01056164156","description":"提交成功"}';
 		$resultArr = json_decode($result,true);
 		if($resultArr['result']){
 			$resultArr['uniqueId'] = '';
 			$resultArr['clid']     = '';
 		}
 		$data['ctrl_id'] = $ctrl_id;
 		$data['address'] = $address;
 		$data['user_name'] = $user_name;
 		$data['warn_id']  = $warn_id;//[1：有害气体告警；2：火警；3：漏水告警；4：闯入告警； 5:可燃气体报警 ; 6:SOS告警;
 		$data['warning_time']= $warn_time;
 		$data['message']	= 	$message;
 		$data['room_name']  = $room_name;
		$callout->saveSubmitData($data,$resultArr);
		echo json_encode(array(
				"result" => $resultArr['result'],
				"description" => $resultArr['description']
		));
		exit;
	}
	
	public function actionReceive()
	{
 		$dataArr = $_REQUEST;
//  		$dataArr = '{"start_time":"1458194475","end_time":"1458194505","status":"21","uniqueId":"d40911ed5c3903377ed6634db51e3549","answer_time":""}';
//  		$dataArr = json_decode($dataArr,true);
 		Yii::log('CalloutController::actionReceive request data is :'.json_encode($dataArr),'info');
 		$uniqueId = isset($dataArr['uniqueId'])?$dataArr['uniqueId']:'';
 		if($uniqueId == "") exit("error");
		$content = array(
				"start_time"  => isset($dataArr['start_time'])?$dataArr['start_time']:'',
				"end_time"    => isset($dataArr['end_time'])?$dataArr['end_time']:'',
				"uniqueId"    => isset($dataArr['uniqueId'])?$dataArr['uniqueId']:'',
				"answer_time" => isset($dataArr['answer_time'])?$dataArr['answer_time']:'',
				"userField"   => isset($dataArr['userField'])?$dataArr['userField']:'',
				"status"      => isset($dataArr['status'])?$dataArr['status']:''
		);
		$callout = new CallModule();
		$callout->saveHangData($content);
		echo "success";
	}		
}