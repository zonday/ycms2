<?php
/* @var $this ChannelController */
/* @var $model Channel */

$this->pageTitle = '查看栏目';

$this->breadcrumbs=array(
	'栏目'=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'栏目列表', 'url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建栏目', 'url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'更新栏目', 'url'=>array('update', 'id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'移动至回收站','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true, 'submit'=>array('trash','id'=>$model->id))),
	array('label'=>'彻底删除','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true, 'submit'=>array('delete','id'=>$model->id),'confirm'=>'删除这个栏目将会导致该栏目下的所有内容将被删除！请谨慎操作！')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看栏目</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php $this->widget('bootstrap.widgets.TbDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'title',
		'name',
		array(
			'name'=>'type',
			'value'=>$model->typeList[$model->type],
		),
		array(
			'name'=>'model',
			'value'=>isset($model->modelList[$model->model])? $model->modelList[$model->model] : null,
		),
		array(
			'name'=>'keywords',
		),
		array(
			'name'=>'description',
		),
		array(
			'name'=>'create_time',
			'type'=>'datetime',
		),
		array(
			'name'=>'update_time',
			'type'=>'datetime',
		),
	),
)); ?>
