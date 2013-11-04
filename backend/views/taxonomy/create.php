<?php
/* @var $this TaxonomyController */

$this->pageTitle='创建分类';

$this->breadcrumbs=array(
	'分类' => array('index'),
	'创建'
);

$this->menu=array(
	array('label'=>'分类列表','url'=>array('index'), 'icon'=>'list'),
);
?>

<div class="page-header">
	<h1><i class="icon-plus"></i> 创建分类</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>
<?php $this->renderPartial('_form', array('model' => $model)); ?>
