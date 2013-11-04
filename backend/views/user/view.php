<?php
$this->pageTitle = '查看用户';

$this->breadcrumbs=array(
	'用户'=>array('index'),
	$model->username,
);

$this->menu=array(
	array('label'=>'用户列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建用户','url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'更新用户','url'=>array('update','id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'删除用户','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true,'submit'=>array('delete','id'=>$model->id),'confirm'=>'确定要删除这条数据吗?')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看用户</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'username',
		'email',
		'nickname',
		array(
			'name'=>'status',
			'value'=>$model->statusList[$model->status],
		),
		array(
			'name' => 'roleNames',
			'value' => implode(',', $model->roleList),
		),
		array (
			'name'=>'create_time',
			'type'=>'datetime',
		),
		array (
			'name'=>'update_time',
			'type'=>'datetime',
		),
		array (
			'name'=>'login_time',
			'value' => $model->login_time ? Yii::app()->format->datetime($model->login_time) : '从未登录',
		)
	),
)); ?>
