<?php
$this->pageTitle = '查看术语';

$this->breadcrumbs=array(
	'分类'=>array('taxonomy/index'),
	$model->taxonomy->name=>array('taxonomy/view', 'id'=>$model->taxonomy->id),
	'查看术语',
);

$this->menu=array(
	array('label'=>$model->taxonomy->name,'url'=>array('taxonomy/view', 'id'=>$model->taxonomy->id), 'icon'=>'list'),
	array('label'=>'创建术语','url'=>array('create', 'taxonomy_id'=>$model->taxonomy->id), 'icon'=>'plus'),
	array('label'=>'更新术语','url'=>array('update','id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'删除术语','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true, 'submit'=>array('delete','id'=>$model->id),'confirm'=>'删除这个术语将会导致该术语下的所有内容关系将被删除！请谨慎操作！')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看术语</h1>
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
		array(
			'name'=>'taxonomy_id',
			'value'=>$model->taxonomy->name,
		),
		'name',
		'slug',
		'description',
		'weight',
	),
)); ?>
