<?php
$this->pageTitle = '更新术语';

$this->breadcrumbs=array(
	'分类'=>array('taxonomy/index'),
	$model->taxonomy->name=>array('taxonomy/view', 'id'=>$model->taxonomy->id),
	'更新术语',
);

$this->menu=array(
	array('label'=>$model->taxonomy->name,'url'=>array('taxonomy/view', 'id'=>$model->taxonomy->id), 'icon'=>'list'),
	array('label'=>'创建术语','url'=>array('create', 'taxonomy_id'=>$model->taxonomy->id), 'icon'=>'plus'),
	array('label'=>'查看术语','url'=>array('view','id'=>$model->id), 'icon'=>'eye-open'),
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新术语</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>