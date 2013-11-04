<?php
/* @var $this LinkController */
/* @var $model Link */
$this->pageTitle = '链接列表';

$this->breadcrumbs=array(
	'链接',
);
?>

<div class="page-header">
	<h1><i class="icon-link"></i> 链接</h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus"></i> 创建链接', array('create'), array('class' => 'btn btn-primary')); ?>
	</div>
</div>
<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'type'=>'horizontal',
));
$actions = array(
	'' => '批量操作',
	'delete' => '删除',
);?>
<?php
$this->widget('bootstrap.widgets.TbExtendedGridView',array(
	'id'=>'file-grid',
	'dataProvider'=>$model->with('category')->search(),
	'bulkActions' => array(
		'class' => 'YBulkActions',
		'align' => 'left',
		'actionButtons' => $actions,
	),
	'filter'=>$model,
	'columns'=>array(
		array(
			'name'=>'id',
			'class'=>'CCheckBoxColumn',
			'selectableRows'=>2,
			'checkBoxHtmlOptions'=>array('name' => 'ids[]'),
		),
		'name',
		array(
			'name'=>'category_id',
			'value'=>'$data->category ? $data->category->name : null',
			'filter'=>$model->getCategoryList(),
		),
		array(
			'name'=>'visible',
			'type'=>'boolean',
			'filter'=>array('0'=>'否', '1'=>'是'),
			'class' => 'bootstrap.widgets.TbToggleColumn',
			'toggleAction' => 'link/toggle',
			'checkedButtonLabel'=>'可见',
			'uncheckedButtonLabel'=>'不可见',
			'name' => 'visible',
		),
		array(
			'name'=>'create_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'filter'=>YUtil::timeFilterList(),
		),
		array(
			'class'=>'YInputColumn',
			'name'=>'weight',
			'inputHtmlOptions'=>array('name'=>'weight')
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
		),
	),
));
?>
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