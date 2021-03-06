<div class="container">
	<form class="form-horizontal" role="form" method="post">
		<input type="hidden" name="userid" value="<?=$model['userid']?>">
		<fieldset>
			<legend>匿名聊天设置</legend>
			<div class="form-group">
				<label for="active_time_start" class="col-sm-2 control-label">开始时间</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['active_time_start'])?$model['active_time_start']:''?>" id="active_time_start" name="active_time_start">
				</div>
			</div>
			<div class="form-group">
				<label for="active_time_end" class="col-sm-2 control-label">结束时间</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['active_time_end'])?$model['active_time_end']:''?>" id="active_time_end" name="active_time_end">
				</div>
			</div>
			<!-- <div class="form-group">
				<label for="wait_time_out" class="col-sm-2 control-label">匹配超时</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=isset($model['wait_time_out'])?$model['wait_time_out']:'300'?>" id="wait_time_out" name="wait_time_out" min="60" max="300" readonly>
					<span class="help-block">单位为秒</span>
				</div>
			</div> -->
			<div class="form-group">
				<label for="chat_time_out" class="col-sm-2 control-label">聊天时间限制</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=isset($model['chat_time_out'])?$model['chat_time_out']:''?>" id="chat_time_out" name="chat_time_out" min="300" max="600">
					<span class="help-block">单位为秒</span>
				</div>
			</div>
			<div class="form-group">
				<label for="chat_superior_limit" class="col-sm-2 control-label">一天内聊天次数上限</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=isset($model['chat_superior_limit'])?$model['chat_superior_limit']:''?>" id="chat_superior_limit" name="chat_superior_limit">
				</div>
			</div>
			<div class="form-group">
				<label for="share_number" class="col-sm-2 control-label">需要的分享人数</label>
				<div class="col-sm-10">
					<input type="number" class="form-control" required value="<?=isset($model['share_number'])?$model['share_number']:''?>" id="share_number" name="share_number">
				</div>
			</div>
			<div class="form-group">
				<label for="invalid_time_text" class="col-sm-2 control-label">未到有效时间的回复</label>
				<div class="col-sm-10">
					<span class="help-block">开始时间为 {$start_time} / 结束时间为 {$end_time}</span>
					<textarea class="form-control" rows="3"  required id="invalid_time_text" name="invalid_time_text"><?=isset($model['invalid_time_text'])?$model['invalid_time_text']:'同学你好，现在重磅推出匿名CP配对交友活动。
为增加体验乐趣以及匹配成功率，活动仅每晚{$start_time}-{$end_time}点开放CP聊天，
同学们不要错过时间。脱单黑科技，告别单身狗，欢迎奔走相告，拉同学一起来玩。'?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label for="subscribe_text" class="col-sm-2 control-label">关注的回复</label>
				<div class="col-sm-10">
					<textarea class="form-control" rows="3"  required id="subscribe_text" name="subscribe_text"><?=isset($model['subscribe_text'])?$model['subscribe_text']:'嘿，你来啦：\n回复“交友”，开始召唤神秘的对方吧！.'?></textarea>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>公众号设置</legend>
			<div class="form-group">
				<label for="subscription_account_app_id" class="col-sm-2 control-label">appId</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['subscription_account_app_id'])?$model['subscription_account_app_id']:''?>" id="subscription_account_app_id" name="subscription_account_app_id">
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_account_app_secret" class="col-sm-2 control-label">appSecret</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['subscription_account_app_secret'])?$model['subscription_account_app_secret']:''?>" id="subscription_account_app_secret" name="subscription_account_app_secret">
				</div>
			</div>
			<div class="form-group">
				<label for="subscription_account_ghid" class="col-sm-2 control-label">ghid</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['subscription_account_ghid'])?$model['subscription_account_ghid']:''?>" id="subscription_account_ghid" name="subscription_account_ghid">
				</div>
			</div>
			<!-- <div class="form-group">
				<label for="service_account_app_id" class="col-sm-2 control-label">服务号appId</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['service_account_app_id'])?$model['service_account_app_id']:''?>" id="service_account_app_id" name="service_account_app_id">
				</div>
			</div>
			<div class="form-group">
				<label for="service_account_app_secret" class="col-sm-2 control-label">服务号appSecret</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['service_account_app_secret'])?$model['service_account_app_secret']:''?>" id="service_account_app_secret" name="service_account_app_secret">
				</div>
			</div>
			<div class="form-group">
				<label for="service_account_ghid" class="col-sm-2 control-label">服务号ghid</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" required value="<?=isset($model['service_account_ghid'])?$model['service_account_ghid']:''?>" id="service_account_ghid" name="service_account_ghid">
				</div>
			</div> -->
		</fieldset>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-default">保存</button>
			</div>
		</div>
	</form>
</div>
<script src="<?=$base_url?>assets/laydate/laydate.js"></script>
<script>
laydate.render({
	type: 'time',
	elem: '#active_time_start' //指定元素
});
laydate.render({
	type: 'time',
	elem: '#active_time_end' //指定元素
});
</script>
