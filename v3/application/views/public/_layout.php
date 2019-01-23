<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Starter Template for Bootstrap</title>
	<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<style>
		.main {
			margin-top: 50px;
			padding-top: 10px;
		}
		input[type="date"].form-control,
		input[type="time"].form-control,
		input[type="datetime-local"].form-control,
		input[type="month"].form-control {
			line-height: 20px;
		}
	</style>
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?=site_url('Admin/index')?>">Project name</a>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li><a href="<?=site_url('Admin/userlist')?>">用户</a></li>
					<li class="active"><a href="<?=site_url('Admin/setup')?>">角色</a></li>
					<li><a href="#contact">路由</a></li>
					<li><a href="#contact">菜单</a></li>
				</ul>
				<?php if(isset($username)):?>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?=$username?><span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="<?=site_url('Admin/changepassword')?>">修改密码</a></li>
							<li><a href="<?=site_url('Admin/logout')?>">注销</a></li>
						</ul>
					</li>
				</ul>
				<?php endif;?>
			</div><!--/.nav-collapse -->
		</div>
	</nav>

	<div class="main">
		<div class="container">
		<?php if (isset($alert_msg)):?>
		<div class="alert alert-<?=$alert_msg['type']?> alert-dismissable">
			<button type="button" class="close" data-dismiss="alert"
					aria-hidden="true">
				&times;
			</button>
			<?=$alert_msg['msg']?>
		</div>
		<?php endif;?>
		<?= $tpl ?>
		</div>
	</div>

	<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script>
		$("[request-confirm]").on("click", function() {
			let message = $(this).attr("request-confirm");
			if (!confirm(message)) {
				return false;
			}
			let requestMethod = $(this).attr("request-method");
			if (requestMethod === "get") {
				console.log($(this).data());
				var formdata = $(this).data();
				// for (item in formdata) {
				// 	let input = document.createElement("input");
				// 	input.name = 
				// }
				// formdata.forEach(function(item, index){
				// 	console.log(index+":"+item);
				// });
				for (let key in formdata) {
					console.log(key, formdata[key]);
				}
				location = $(this).attr("href");
			} else {
				var form = document.createElement("form");
				form.action = $(this).attr("href");
				form.method = "post";
				form.style = "display:none;";
				form.id = "asdqwe";
				var formdata = $(this).data();
				for (let key in formdata) {
					console.log(key, formdata[key]);
					let input = document.createElement("input");
					input.name = key;
					input.value = formdata[key];
					form.appendChild(input);
				}
				document.body.appendChild(form);
				return false;
			}
		});
	</script>
</body>
</html>
