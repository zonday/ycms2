<?php

$channel = $this->getChannel();
$this->pageTitle = $channel->title;

$this->breadcrumbs=$this->generateBreadcrumb($channel);
$this->breadcrumbs[]=$channel->title;
?>

<div class="page-header">
	<h1> <?php echo $this->pageTitle; ?></h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus"></i> 创建' . $channel->modelName, array('create', 'channel'=>$channel->id), array('class' => 'btn btn-primary')); ?>
	</div>
</div>

<?php echo $this->renderPartial('/content/' . $this->getViewDirectory($model) . '/_grid', array('model'=>$model, 'channel'=>$channel)); ?>