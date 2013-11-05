<?php
$channel = $this->getChannel();
$this->pageTitle = '创建' . $channel->modelName;

$this->breadcrumbs=$this->generateBreadcrumb($channel);
$this->breadcrumbs[$channel->title]=array('index', 'channel'=>$channel->id);
$this->breadcrumbs[]=$this->pageTitle;

$this->menu=array(
	array('label'=>$channel->title, 'url'=>array('index', 'channel'=>$channel->id), 'icon'=>'list'),
);
?>

<div class="page-header">
	<h1><i class="icon-plus"></i> <?php echo $this->pageTitle; ?></h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('/content/' . $this->getViewDirectory($model) . '/_form', array('model'=>$model, 'channel'=>$channel)); ?>