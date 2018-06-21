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
			url:'xxx',
			dataType:'json',
			success:function(data){
				$('title').html(data.code);
				$('#msg').html(data.msg);
			}
		});
	}, 1000);
});
</script>
</body>
</html>
