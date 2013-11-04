<?php
$this->pageTitle = '查看Banner';

$this->breadcrumbs=array(
	'Banner'=>array('index'),
	$model->name,
);

$this->menu=array(
	array('label'=>'Banner列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建Banner','url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'更新Banner','url'=>array('update','id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'删除Banner','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true,'submit'=>array('delete','id'=>$model->id),'confirm'=>'确定要删除这条数据吗?')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看Banner</h1>
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
		array(
			'value'=>$model->imageFile->getImage(File::IMAGE_ORIGIN, array('style'=>'max-width: 600px')),
			'type'=>'raw',
		),
		'id',
		'name',
		array(
			'name'=>'visible',
			'type'=>'boolean',
		),
		'link_href',
		array(
			'name'=>'link_target',
			'value'=>$model->targetList[$model->link_target],
		),
		array(
			'name'=>'category_id',
			'value'=>isset($model->categoryList[$model->category_id]) ? $model->categoryList[$model->category_id] : null,
		),
		'weight',
	),
)); ?>