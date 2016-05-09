<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<title>爱洗吧</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/turingcat/css/bootstrap.min.css">
	<script src="/turingcat/js/jquery-1.7.2.min.js"></script>
	<script src="/turingcat/js/bootstrap.min.js"></script>
	<script src="/turingcat/js/turingcat.js"></script>
</head>
<body>
<div style="height:100%;">
	<div class="modal" style="display:block;top:30%;">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">提示</h4>
	      </div>
	      <div class="modal-body">
	        <p><?php echo $msg;?></p>
	      </div>
	      <div class="modal-footer">
	        <button type="button" id="washclose" class="btn btn-primary">关闭</button>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div>
</div>
</body>
<script type="text/javascript">
$(document).ready(function(){
		var trc=new Turingcat();
	    $('#washclose').on("click",function(){
	        trc.closeWindow();
	    });
});
</script>
</html>
