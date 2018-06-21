<html>
<head>
<title>start...</title>
</head>
<body>
<div id="msg">error!</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
//document.title="XXXX";
$(function(){	
	window.setInterval(function(){
		$.ajax({
			url:'http://www.xyzelec.com/krang/test',
			dataType:'json',
            type:'post',
            data:{msg:"嘻嘻嘻"},
			success:function(data){
				$('title').html(data.code);
				$('#msg').html(data.msg);
			}
		});
	}, 5000);
});
</script>
</body>
</html>
