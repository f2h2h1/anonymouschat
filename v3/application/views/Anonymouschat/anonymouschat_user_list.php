<h2>用户列表</h2>
<div class="table-block">
	<div class="row">
		<div class="col-sm-4 col-md-3">
			<!-- 顶部按钮 -->
			<div class="btn-toolbar" role="toolbar">
				<div class="btn-group">
					<a href="<?=site_url('Anonymouschat/add_anonymouschat_user')?>" class="btn btn-primary">新增</a>
				</div>
			</div>
		</div>
	</div>
	<!-- 数据列表 -->
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<caption></caption>
			<thead>
				<tr>
					<th>id</th>
					<th>用户名</th>
					<th>创建时间</th>
					<th>修改时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($model as $item):?>
				<tr>
					<td><?=$item['id']?></td>
					<td><?=$item['username']?></td>
					<td><?=date('Y-m-d h:i:s', $item['createtime'])?></td>
					<td><?=date('Y-m-d h:i:s', $item['updatetime'])?></td>
					<td>
						<!-- <a href="<?=site_url('Anonymouschat/anonymouschat_user_detail')?>">
							<span class="glyphicon glyphicon-search"></span>
						</a> -->
						<a href="<?=site_url('Anonymouschat/edit_anonymouschat_user')?>/<?=$item['id']?>">
							<span class="glyphicon glyphicon-pencil"></span>
						</a>
						<a href="<?=site_url('Anonymouschat/del_anonymouschat_user')?>" request-confirm="您确定要删除此项吗？" data-id="<?=$item['id']?>">
							<span class="glyphicon glyphicon-trash"></span>
						</a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
	<!-- 分页 -->
</div><!-- table-block end -->