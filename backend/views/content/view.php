<?php
$channel = $this->getChannel();
$this->pageTitle = '查看' . $channel->modelName;

$this->breadcrumbs=$this->generateBreadcrumb($channel);
$this->breadcrumbs[$channel->title]=array('index', 'channel'=>$channel->id);
$this->breadcrumbs[]=$this->pageTitle;

$this->menu=array(
	array('label'=>$channel->title, 'url'=>array('index', 'channel'=>$channel->id), 'icon'=>'list'),
	array('label'=>'创建' . $channel->modelName, 'url'=>array('create', 'channel'=>$channel->id), 'icon'=>'plus'),
	array('label'=>'更新' . $channel->modelName, 'url'=>array('update', 'id'=>$model->id, 'channel'=>$channel->id), 'icon'=>'pencil'),
	array('label'=>'删除' . $channel->modelName, 'url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true,'submit'=>array('delete','id'=>$model->id, 'channel'=>$channel->id),'confirm'=>'确定要删除这条数据吗?')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> <?php echo $this->pageTitle; ?></h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php echo $this->renderPartial('/content/' . $this->getViewDirectory($model) . '/_view', array('model'=>$model)); ?>
