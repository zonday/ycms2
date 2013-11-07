<?php
/* @var $this ChannelController */
/* @var $model Channel */

$this->pageTitle = '栏目列表';

$this->breadcrumbs=array(
	'栏目'
);
?>

<div class="page-header">
	<h1>栏目</h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus icon-white"></i> 创建栏目', array('create'), array('class'=>'btn btn-primary')); ?>
	</div>
</div>
<?php /* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'action' => array('saveWeight'),
));
?>
<?php
$this->widget('bootstrap.widgets.TbGridView', array(
	'id'=>'channel-grid',
	'dataProvider'=>new CArrayDataProvider($model->getTree(), array(
		'pagination'=>array(
			'pageSize'=>50,
		),
	)),
	'rowHtmlOptionsExpression' => 'array("class"=>"level-" .$data->depth)',
	'columns'=>array(
		array(
			'name'=>'title',
			//'type'=>'raw',
			'header'=>$model->getAttributeLabel('title'),
			'htmlOptions'=>array('class'=>'title-column'),
			'value'=>'$data->title',
		),
		array(
			'name'=>'name',
			'header'=>$model->getAttributeLabel('name'),
		),
		/*
		array(
			'name'=>'type',
			'header'=>$model->getAttributeLabel('type'),
			'value'=>'$data->typeList[$data->type]',
		),
		*/
		array(
			'name'=>'model',
			'header'=>$model->getAttributeLabel('model'),
			'value'=>'isset($data->modelList[$data->model]) ? $data->modelList[$data->model] : null',
		),
		array(
			'class'=>'YInputColumn',
			'name'=>'weight',
			'header'=>$model->getAttributeLabel('weight'),
			'inputHtmlOptions' => array('name'=>'weight')
		),
		array(
			'header'=>'操作',
			'type'=>'raw',
			'value'=>'$data->getContentActionLink()',
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'deleteConfirmation'=>'删除这个栏目将会导致该栏目下的所有内容将被删除！请谨慎操作！',
			'template'=>'{view} {update} {delete}',
		),
	),
)); ?>
<div class="text-right">
<?php $this->widget('bootstrap.widgets.TbButton', array(
	'buttonType'=>'submit',
	'htmlOptions'=>array('name'=>'saveWeight'),
	'type'=>'secondary',
	'label'=>'保存权重',
));
?>
</div>
<?php $this->endWidget(); ?>