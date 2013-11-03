<?php
$this->pageTitle = '查看用户';

$this->breadcrumbs=array(
	'用户'=>array('index'),
	'查看'
);
?>

<div class="page-header">
	<h1>查看用户</h1>
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
