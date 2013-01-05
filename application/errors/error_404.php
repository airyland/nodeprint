<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>页面不存在</title>
<style>
*{margin:0;padding:0}
body{font-family:"微软雅黑";}
img{border:none}
a *{cursor:pointer}
ul,li{list-style:none}
table{table-layout:fixed;}
table tr td{word-break:break-all; word-wrap:break-word;}


.cf:after{content: ".";display: block;height: 0;font-size: 0;clear:both;visibility: hidden;}
.cf{zoom: 1;clear:both}

.bg{width:100%;position:absolute;top:0;left:0;height:600px;overflow:hidden}
.cont{margin:0 auto;width:500px;line-height:20px;}
.c1{height:360px;text-align:center}
.c1 .img1{margin-top:180px}
.c1 .img2{margin-top:165px}
.cont h2{text-align:center;color:#555;font-size:18px;font-weight:normal;height:35px}
.c2{height:35px;text-align:center}
.c2 span{display:inline-block;margin:0 4px;font-size:14px;height:23px;color:#626262;padding-top:1px;text-decoration:none;text-align:left}
.c2 span:hover{color:#626262;text-decoration:none;}
.c2 span.home{width:66px;background:url('02.png');padding-left:30px}
.c2 span.home:hover{cursor:pointer;background:url('02.png') 0 -24px}
.c2 span.home:active{background:url('02.png') 0 -48px}
.c2 span.re{width:66px;background:url('03.png');padding-left:30px}
.c2 span.re:hover{cursor:pointer;background:url('03.png') 0 -24px}
.c2 span.re:active{background:url('03.png') 0 -48px}
.c2 span.sr{width:153px;background:url('04.png');padding-left:28px}
.c2 span.sr:hover{cursor:pointer;background:url('04.png') 0 -24px}
.c2 span.sr:active{background:url('04.png') 0 -48px}
.c3{height:180px;text-align:center;color:#999;font-size:12px}
#bf{position:absolute;top:269px;left:0;width:100%}
.bf1{margin:0 auto;width:99px;padding-left:32px}
.bd{height:600px;overflow:hidden}
#box{position:absolute;top:165px;left:0;width:100%;text-align:center}
.bf1{margin:0 auto;width:99px;padding-left:32px}
</style>
</head>
<body>
<div class="bg">
	<div class="cont">
		<div class="c1"><img src="/img/404.png" class="img1" /></div>
		<h2><?php echo $message; ?></h2>
		<div class="c2">
			<a href="#"><span class="re" >再试一次</span></a>
			<a href="/"><span class="home">网站首页</span></a>
			<!--<span class="sr">搜索一下页面相关信息</span>-->
		</div>
		<div class="c3">HTTP Error 404 - 您可能输入了错误的网址，或者该网页已删除或移动
		<br>
		</div>
	</div>
</div>
</body>
</html>