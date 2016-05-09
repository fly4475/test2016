<?php
require_once 'lib/DBManager.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'Records/Order.class.php';
require_once 'configs/config.php';

class paypal_order extends DBManager{

    private $_statis;
    function __construct(){
        $this->_statis = new Order();

        global $g_arr_db_config;
        $this->connectMySqlPara($g_arr_db_config);
    }

    public  function insertOrderInfo($info)
    {
        Log::write("paypal_response_return::recordDetailRequest  ", "log");
        $this->_statis->setParam($info);

        $sql = $this->_statis->getInsertSql();
        $result = $this->executeSql($sql);
        if(!$result){
            Log::write("PayResponce::insertDetailRecord():executeSql() sql: ".$sql." failed", "log");
            return false;
        }
        return true;
    }


}