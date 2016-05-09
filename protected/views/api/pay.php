<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>图灵猫用户支付</title>
<meta name="format-detection" content="telephone=no,email=no">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0, minimal-ui">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10">
<style type="text/css">
body{
	background-color:#f5f5f9;
}
</style>
</head>
<body>
<style>
.am-header {
	display: -webkit-box;
	display: -ms-flexbox;
	display: box;
	width: 100%;
	position: relative;
	padding: 7px 0;
	-webkit-box-sizing: border-box;
	-ms-box-sizing: border-box;
	box-sizing: border-box;
	background: #1D222D;
	height: 50px;
	text-align: center;
	-webkit-box-pack: center;
	-ms-flex-pack: center;
	box-pack: center;
	-webkit-box-align: center;
	-ms-flex-align: center;
	box-align: center
}
.am-header h1 {
	-webkit-box-flex: 1;
	-ms-flex: 1;
	box-flex: 1;
	line-height: 18px;
	text-align: center;
	font-size: 18px;
	font-weight: 300;
	color: #fff
}
.am-content {
  padding-top: 15px;
}
.am-list {
  -webkit-box-sizing: border-box;
  -ms-box-sizing: border-box;
  box-sizing: border-box;
  padding: 0 0 20px;
}
.am-list div.am-list-item {
  padding: 10px 15px;
}
.am-list .am-list-item {
  -webkit-box-sizing: border-box;
  -ms-box-sizing: border-box;
  box-sizing: border-box;
  display: block;
  position: relative;
  z-index: 10;
  margin: 0;
  padding: 10px 30px 10px 15px;
  width: 100%;
  border-top: 1px solid #e5e5e5;
  background-color: #fff;
  color: #000;
  text-align: left;
  vertical-align: middle;
}
.am-section {
  padding: 0 10px 10px;
  overflow: auto;
}
.am-ft-pb-0 {
  padding-bottom: 0!important;
}
.am-password-handy {
  overflow: hidden;
  position: relative;
  margin: 0 0 20px;
  width: 100%;
  padding-bottom: 1px;
}
.am-password-handy .am-password-handy-security {
  display: -webkit-box!important;
  display: -ms-flexbox!important;
  display: box!important;
  padding: 0;
  background-color: #fff;
}
.am-password-handy .am-password-handy-security li {
  -webkit-box-flex: 1;
  -ms-flex: 1;
  box-flex: 1;
  margin-right: -1px;
  border-right: 1px solid #DDD;
  overflow: hidden;
  text-align: center;
}
li {
  list-style: none;
}
.am-password-handy input[type=password], .am-password-handy input[type=tel], .am-password-handy .am-password-handy-security {
  -webkit-box-sizing: border-box;
  -ms-box-sizing: border-box;
  box-sizing: border-box;
  display: block;
  width: 100%;
  height: 44px;
  overflow: hidden;
  padding: 0!important;
  border: 1px solid #DDD;
  border-radius: 4px;
  background-clip: padding-box;
  font-family: Courier,monospace;
  font-size: 24px;
  line-height: 32px;
}
body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, code, form, fieldset, legend, input, textarea, p, blockquote, th, td {
  margin: 0;
  padding: 0;
}
*, :before, :after {
  text-size-adjust: none;
  -webkit-tap-highlight-color: rgba(0,0,0,0);
}
.am-section {
  padding: 0 10px 10px;
  overflow: auto;
}
.am-button[disabled=disabled] {
  color: #E6E6E6;
  background: #F8F8F8;
  border: 1px solid #DEDEDE;
}
.am-button-blue {
  border: 1px solid #3EA3FE;
  color: #FFF;
  background: #3EA3FE;
}
.am-button {
  -webkit-box-sizing: border-box;
  -ms-box-sizing: border-box;
  box-sizing: border-box;
  display: inline-block;
  margin: 0;
  padding: 4px 8px;
  width: 100%;
  text-align: center;
  font-size: 18px;
  line-height: 2;
  border-radius: 4px;
  background-clip: padding-box;
}
.logo {
  position: absolute;
  bottom: 15px;
  left: 50%;
  display: block;
  width: 137px;
  height: 44px;
  margin: 10px auto auto -35px;
  background-image: url("/turingcat/images/fd8166b2.logo.png");
  -webkit-background-size: cover;
  background-size: cover;
}
.am-password-handy input[type=password], .am-password-handy input[type=tel] {
  position: absolute;
  opacity: .01;
  border: 0 none!important;
  left: 0;
  -webkit-appearance: none;
  -webkit-box-sizing: content-box!important;
  -ms-box-sizing: content-box!important;
  box-sizing: content-box!important;
  outline: 0;
}
.am-password-handy input[type=password], .am-password-handy input[type=tel], .am-password-handy .am-password-handy-security {
  -webkit-box-sizing: border-box;
  -ms-box-sizing: border-box;
  box-sizing: border-box;
  display: block;
  width: 100%;
  height: 44px;
  overflow: hidden;
  padding: 0!important;
  border: 1px solid #DDD;
  border-radius: 4px;
  background-clip: padding-box;
  font-family: Courier,monospace;
  font-size: 24px;
  line-height: 32px;
}
input[type=password], input[type=tel] {
  ime-mode: disabled;
}
.am-password-handy .am-password-handy-security li {
  -webkit-box-flex: 1;
  -ms-flex: 1;
  box-flex: 1;
  margin-right: -1px;
  border-right: 1px solid #DDD;
  overflow: hidden;
  text-align: center;
}
li {
  list-style: none;
}
.am-password-handy .am-password-handy-security i:empty {
  margin: 16px 2px 0;
  width: 10px;
  height: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  background-clip: padding-box;
  background-color: #000;
}
.am-password-handy .am-password-handy-security i {
  display: inline-block;
  width: 14px;
  overflow: hidden;
  line-height: 42px;
  font-style: normal;
  visibility: hidden;
}
</style>
<header class="am-header">
	<h1>图灵猫用户支付确认</h1>
</header>
<div class="am-content">
	<form id="cashier" action="/api/consume" method="post" novalidate="novalidate" data-widget-cid="widget-0">
		<div class="am-list am-list-flat-chip">
			<div class="am-list-item"><?php echo $money;?> 元 </div>
		</div>
		<div class="am-section am-ft-pb-0">
			
	       <div class="J-keyboard am-password-handy">
	       		<input type="hidden" name="orderid" value='<?php echo $orderid;?>'>
	       		<input type="tel" class="J-pwd J-needsclick J-needsfocus " id="spwd_unencrypt" name="spwd_unencrypt" maxlength="6" pattern="\d*" autocomplete="off">
	            <input type="hidden" id="spwd" name="spwd" class="J-encryptpwd" value="" data-widget-cid="widget-1">
	           <ul class="am-password-handy-security">
			       <li><i></i></li>
			        <li><i></i></li>
			        <li><i></i></li>
			        <li><i></i></li>
			        <li><i></i></li>
			        <li><i></i></li>
	            </ul>
	        </div>
		</div>
		<div class="am-section">
	        <button type="submit" class="J-button-submit am-button am-button-blue" seed="needConfirm-submit">确认付款</button>
	    </div>
	</form>
</div>
<!-- <footer> -->
  <!--    <div class="logo" style="position: absolute; left: 50%; margin-left: -65px; margin-top: 10px; margin-bottom: auto;"></div>  -->      
<!-- </footer> -->
</body>
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
var isInputEvent = "oninput" in document ? true : false;
var inputEvent = isInputEvent ? "input" : 'keyup';
var pwd = $(".J-pwd");
	  
var isPaste = false;
var encryptpwd = $('.J-encryptpwd');
var icons = $(".am-password-handy-security li i");

var showIcon=   function() {
	for (var b = pwd.val().length, c = icons.length; c--;)icons[c].style.visibility = b > c ? "visible" : "hidden";
	};
	
var clean = function(){
      pwd.val('');
      encryptpwd.val('');
      showIcon();
    };
	  
	var setRange = function(el){
	el.setSelectionRange(parseInt(el.value.length+1),parseInt(el.value.length+1))
	};
	  
      pwd.on('focus',function(e){
      var el= e.target;
      setRange(el);
    });
    pwd.on('keydown',function(e){
      var el = e.target;
      if (e.which === 8 || e.which === 46) clean();
      setRange(el);
    });
    pwd.on('keyup',function(e){
      var el = e.target;
      setRange(el);
    });
    pwd.on('paste',function(e){
      isPaste = true;
    });
    encryptpwd.val('');

    pwd.on(inputEvent,function(e){
      var el= e.target;
      if(isPaste) clean();
      isPaste=false;
      setRange(el);
      var currentPW =  el.value.split('*').slice(-1).toString();

      var encryptPW =  encryptpwd[0].value;
      if(currentPW.length === 0) {
        encryptpwd[0].value = encryptpwd[0].value.split(',').slice(0, el.value.length).toString();
      } else {
        //新增
        for (var i=0;i < currentPW.length;i++) {
          var outkey = currentPW[i];
          if(encryptpwd.val() === ''){
            encryptpwd.val(outkey);
          } else {
            encryptpwd[0].value += ',' + outkey;
          }
        }
        el.value = el.value.replace(/\S/g,"*");
      }
      showIcon();
    });
    var spwd = $('input.J-pwd');
    var buttonSubmit = $('.J-button-submit');
    function checkSPWD() {
        if (spwd.val().length == 6) {
            buttonSubmit.removeAttr('disabled');
        } else {
            buttonSubmit.attr('disabled', 'disabled');
        }
    }
    checkSPWD();
    spwd.on('keyup keydown change input',function(e){
        checkSPWD();
    });



</script>

</html>