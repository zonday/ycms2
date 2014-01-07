<?php
/* @var $this TaxonomyController
 */
$this->pageTitle='分类列表';

$this->breadcrumbs=array(
	'分类',
);
?>

<div class="page-header">
	<h1><i class="icon"></i> 分类</h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus icon-white"></i> 创建分类', array('create'), array('class'=>'btn btn-primary')); ?>
	</div>
</div>

<?php /* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'action' => array('saveWeight'),
));
?>
<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'taxonomy-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'id',
			'headerHtmlOptions'=>array('class'=>'id-column'),
		),
		array(
			'name'=>'name',
			'headerHtmlOptions'=>array('class'=>'name-column'),
		),
		array(
			'name'=>'slug',
			'headerHtmlOptions'=>array('class'=>'slug-column'),
		),
		array(
			'name'=>'description',
			'headerHtmlOptions'=>array('class'=>'description-column'),
		),
		array(
			'class'=>'YInputColumn',
			'name'=>'weight',
			'inputHtmlOptions' => array('name'=>'weight')
		),
		array(
			'class'=>'CLinkColumn',
			'label'=>'<i class="icon-plus"></i> 添加术语',
			'urlExpression'=>'Yii::app()->createUrl("term/create", array("taxonomy_id"=>$data->id))',
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'deleteConfirmation' => '删除这个分类将会导致该分类下的所有术语将被删除！且术语下的内容关系也将被删除，请谨慎操作！',
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