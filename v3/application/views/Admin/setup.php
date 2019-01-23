<style>

</style>
<div class="container">
	<form class="form-horizontal" role="form" method="post">
		<fieldset>
			<legend>匿名聊天设置</legend>
			<div class="form-group">
				<label for="active_time_start" class="col-sm-2 control-label">开始时间</label>
				<div class="col-sm-10">
					<input type="time" class="form-control" required value="<?=$active_time_start?>" id="active_time_start" name="active_time_start">
				</div>
			</div>
			<div class="form-group">
				<label for="active_time_end" class="col-sm-2 control-label">结束时间</label>
				<div class="col-sm-10">
					<input type="time" class="form-control" required value="<?=$active_time_end?>" id="active_time_end" name="active_time_end">
				</div>
			</div>
			<div class="form-group">
				<label for="wait_time_out" class="col-sm-2 control-label">匹配超时</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=$wait_time_out?>" id="wait_time_out" name="wait_time_out" min="60" max="300">
					<span class="help-block">单位为秒</span>
				</div>
			</div>
			<div class="form-group">
				<label for="chat_time_out" class="col-sm-2 control-label">聊天时间限制</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=$chat_time_out?>" id="chat_time_out" name="chat_time_out" min="300" max="600">
					<span class="help-block">单位为秒</span>
				</div>
			</div>
			<div class="form-group">
				<label for="chat_superior_limit" class="col-sm-2 control-label">一天内聊天次数上限</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=$chat_superior_limit?>" id="chat_superior_limit" name="chat_superior_limit">
				</div>
			</div>
			<div class="form-group">
				<label for="share_number" class="col-sm-2 control-label">需要的分享人数</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=$share_number?>" id="share_number" name="share_number">
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>公众号设置</legend>
			<div class="form-group">
				<label for="subscription_account_app_id" class="col-sm-2 control-label">订阅号appId</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$subscription_account_app_id?>" id="subscription_account_app_id" name="subscription_account_app_id">
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_account_app_secret" class="col-sm-2 control-label">订阅号appSecret</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$subscription_account_app_secret?>" id="subscription_account_app_secret" name="subscription_account_app_secret">
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_account_ghid" class="col-sm-2 control-label">订阅号ghid</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$subscription_account_ghid?>" id="subscription_account_ghid" name="subscription_account_ghid">
				</div>
			</div>
			<div class="form-group">
				<label for="service_account_app_id" class="col-sm-2 control-label">服务号appId</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$service_account_app_id?>" id="service_account_app_id" name="service_account_app_id">
				</div>
			</div>
			<div class="form-group">
				<label for="service_account_app_secret" class="col-sm-2 control-label">服务号appSecret</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$service_account_app_secret?>" id="service_account_app_secret" name="service_account_app_secret">
				</div>
			</div>
			<div class="form-group">
				<label for="service_account_ghid" class="col-sm-2 control-label">服务号ghid</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=$service_account_ghid?>" id="service_account_ghid" name="service_account_ghid">
				</div>
			</div>
		</fieldset>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-default">保存</button>
			</div>
		</div>
	</form>
</div>
<script></script>