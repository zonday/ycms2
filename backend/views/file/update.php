<?php
$this->pageTitle = '更新文件';

$this->breadcrumbs=array(
	'文件'=>array('index'),
	$model->getName()=>array('view','id'=>$model->id),
	'更新',
);

$this->menu=array(
	array('label'=>'文件列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'上传文件','url'=>array('upload'), 'icon'=>'upload'),
	array('label'=>'查看文件','url'=>array('view','id'=>$model->id), 'icon'=>'eye-open'),
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新文件</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form',array('model'=>$model, 'isAjax'=>$isAjax)); ?>