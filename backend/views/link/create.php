<?php
$this->pageTitle = '创建链接';

$this->breadcrumbs=array(
	'链接'=>array('index'),
	'创建',
);

$this->menu=array(
	array('label'=>'链接列表','url'=>array('index'), 'icon'=>'list'),
);
?>

<div class="page-header">
	<h1><i class="icon-plus"></i> 创建链接</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>