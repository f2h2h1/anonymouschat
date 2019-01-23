<h2>用户列表</h2>
<div class="table-block">
	<!-- 数据列表 -->
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<caption></caption>
			<thead>
				<tr>
					<th>id</th>
					<th>用户名</th>
					<th>角色</th>
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
					<td><?=$item['role']?></td>
					<td><?=$item['createtime']?></td>
					<td><?=$item['updatetime']?></td>
					<td>
						<a href="<?=site_url('Admin/userdetail')?>" request-confirm="您确定要删除此项吗？" request-method="post" data-id="<?=$item['id']?>" data-name="asd"><span class="glyphicon glyphicon-search"></span></a>
						<a href="<?=site_url('Admin/edituser')?>"><span class="glyphicon glyphicon-pencil"></span></a>
						<a href="<?=site_url('Admin/deluser')?>" request-confirm="您确定要删除此项吗？" request-method="post" data-id="<?=$item['id']?>">
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