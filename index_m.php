<?php
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
?>

<head>
   <meta charset="utf-8">
   <title></title>
   
    <style>
	#dragover{
		width:200px;
		height:100px;
		padding-top:65px;
		border:5px dashed #CCC;

		font-family:Verdana;
		text-align:center;
	}    
 </style>

  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
 
</head>

 <body crossOrigin="anonymous">

 
   <div id="dragover" crossOrigin="anonymous"><font color="#FF0000"><b>WORK</b></font><br>event: <b>dragleave</b></div>
   
   
<script>
$(document).ready(function() {

$('#dragover').bind({
	
	
	dragover: function(e) { e.preventDefault(); e.stopPropagation();
		console.log('dragover');
		$("#dragover").html('<font color=#FF0000><b>WORK</b></font><br />event: <b>dragover</b>');
		
	},
	
	dragenter: function(e) { e.preventDefault(); e.stopPropagation();
		$("#dragover").html('<font color=#FF0000><b>WORK</b></font><br />event: <b>dragenter</b>');
	},

	dragleave: function(e) { e.preventDefault(); e.stopPropagation(); 
		$("#dragover").html('<font color=#FF0000><b>WORK</b></font><br />event: <b>dragleave</b>');
	},
	
	 drop: function(e) { e.preventDefault(); e.stopPropagation();
		console.log(e);
		e.dataTransfer = e.originalEvent.dataTransfer;
		var data = e.dataTransfer.getData("Text");
		//var data = e.dataTransfer.getData("url");
		console.log(data);
		//alert(data);
		//console.log(e.dataTransfer.types);
		$("#dragover").html('<font color=#FF0000><b>WORK</b></font><br />event: <b>' + data + '</b>');
     },
	
});


});
</script>

   <br>
   <img src="http://msdrop.com/share/fav_16x16.png" border="0">
   
   
<div id="deb" style="background:#FFFFFF;padding:20px; border:0px dashed #C2C2C2"></div>
   
<!-- <iframe frameborder="1" scrolling="auto" style="height:250px;width:100%;" src="index.php?<?php //echo filemtime('index.php');?>"></iframe> -->

<?php
  $salt = 'sdf.n34l5n34#$%dcmnsdn34#%$5ndfm,gn.dfsd#$%34m,ndf';
  //lang=[ru/en]
  $req_str = 'time='.time().'&lang=ru&measure=[METRIC/INCH]&user=<USERMAIL>&user_status=[PAID/DEMO/ADMIN]';
  $full_req_str = $req_str.'&pass='.md5($req_str.$salt);
?>

<iframe frameborder="1" scrolling="auto" style="height: 650px; width: 50%; position: fixed; top: 10px; left: 300px;" src="index.php?<?php echo $full_req_str;?>"></iframe>


<!-- <iframe frameborder="1" scrolling="auto" style="height:250px;width:100%;" src="http://online.cableproject.net/widgets/tree/index.php?<?php // echo $full_req_str;?>"></iframe> -->

<!-- sandbox="allow-same-origin allow-scripts" -->

</body>

