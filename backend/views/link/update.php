<?php
/* @var $this LinkController */
/* @var $model Link */
$this->pageTitle = '更新链接';

$this->breadcrumbs=array(
	'链接'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'更新',
);

$this->menu=array(
	array('label'=>'链接','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建链接','url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'查看链接','url'=>array('view','id'=>$model->id), 'icon'=>'eye-open'),
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新链接</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>