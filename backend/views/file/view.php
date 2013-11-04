<?php
$this->pageTitle = '查看文件';

$this->breadcrumbs=array(
	'文件'=>array('index'),
	$model->getName(),
);

$this->menu=array(
	array('label'=>'文件列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'上传文件','url'=>array('upload'), 'icon'=>'plus'),
	array('label'=>'更新文件','url'=>array('update','id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'删除文件','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true,'submit'=>array('delete','id'=>$model->id),'confirm'=>'确定要删除这条数据吗?')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看文件</h1>
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
		array(
			'type'=>'raw',
			'value'=>$model->getIcon(),
		),
		'id',
		'name',
		array(
			'name'=>'user_id',
			'value'=>$model->user->nickname,
		),
		'caption',
		'description',
		'path',
		'url',
		array(
			'name'=>'size',
		),
		array (
			'name'=>'create_time',
			'type'=>'datetime',
		),
		array (
			'name'=>'update_time',
			'type'=>'datetime',
		)
	),
)); ?>
