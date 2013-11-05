<?php
/* @var $this ChannelController */
/* @var $model Channel */

$this->pageTitle = '更新栏目';

$this->breadcrumbs=array(
	'栏目'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'更新',
);

$this->menu=array(
	array('label'=>'栏目列表', 'url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建栏目', 'url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'查看栏目', 'url'=>array('view', 'id'=>$model->id), 'icon'=>'eye-open'),
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新栏目</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>