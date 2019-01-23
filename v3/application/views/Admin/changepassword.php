<form class="form-horizontal" role="form" action="<?=site_url('Admin/changepassword')?>" method="post">
	<div class="form-group">
		<label for="old_password" class="col-sm-2 control-label">旧密码</label>
		<div class="col-sm-10">
			<input type="text" name="old_password" class="form-control" id="old_password" require>
		</div>
	</div>
	<div class="form-group">
		<label for="new_password" class="col-sm-2 control-label">新密码</label>
		<div class="col-sm-10">
			<input type="text" name="new_password" class="form-control" id="new_password" require>
		</div>
	</div>
	<div class="form-group">
		<label for="new_repassword" class="col-sm-2 control-label">确认新密码</label>
		<div class="col-sm-10">
			<input type="text" name="new_repassword" class="form-control" id="new_repassword" require>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-default">修改</button>
		</div>
	</div>
</form>
