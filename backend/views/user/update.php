<?php
/* @var $this UserController */

$this->pageTitle='更新用户';

$this->breadcrumbs=array(
	'用户' => array('user/index'),
	$model->username=>array('view','id'=>$model->id),
	'更新',
);

$this->menu=array(
	array('label'=>'用户列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建用户','url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'查看用户','url'=>array('view','id'=>$model->id), 'icon'=>'eye-open'),
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新用户</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>