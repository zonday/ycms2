<?php
/* @var $form TbActiveForm */
?>
<div class="text-right">
<button type="button" class="btn" data-toggle="collapse" data-target="#filter-form">
筛选
</button>
</div>
<div class="filter-form collapse out <?php echo isset($_GET['filter']) ? 'in': '';?>" id="filter-form">
	<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
		'action'=>Yii::app()->createUrl($this->route, array('channel'=>$channel->id)),
		'method'=>'get',
		'type'=>'horizontal',
	));
	?>

	<?php
	$this->widget('YTaxonomyFilterWidget', array(
		'model'=>$model,
	));
	?>

	<?php echo $form->dropDownListRow($model, 'status', $model->getStatusList(), array('empty'=>'', 'class'=>'span1')); ?>

	<?php echo $form->radioButtonListInlineRow($model, 'sticky', array('否', '是'), array('separator'=>' ')); ?>

	<?php echo $form->radioButtonListInlineRow($model, 'promote', array('否', '是'), array('separator'=>' ')); ?>

	<div class="text-right">
		<?php echo CHtml::submitButton('筛选', array('name'=>'filter', 'class'=>'btn btn-primary')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
<?php ?>
<?php if (isset($_GET['filter'])): ?>
<h3>筛选结果：<small><?php echo CHtml::link('重置', array($this->route, 'channel'=>$channel->id)); ?></small></h3>
<?php endif; ?>
<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'type'=>'horizontal',
));
$actions = array(
	'' => '批量操作',
	'sticky'=>'置顶',
	'unsticky'=>'取消置顶',
	'promote'=>'推荐至首页',
	'demote'=>'取消推荐至首页',
	'public'=>'公开',
	'draft'=>'待审核',
	'delete' => '删除',
);?>
<?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	'id'=>strtolower($channel->model).'-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'bulkActions' => array(
		'class' => 'YBulkActions',
		'align' => 'left',
		'actionButtons' => $actions,
	),
	'columns'=>array(
		array(
			'name'=>'id',
			'class'=>'CCheckBoxColumn',
			'selectableRows'=>2,
			'checkBoxHtmlOptions'=>array('name' => 'ids[]'),
		),
		array(
			'name'=>'id',
			'headerHtmlOptions'=>array('class'=>'id-column'),
		),
		array(
			'name'=>'title',
			'type'=>'raw',
			'value'=>'CHtml::encode($data->title) . ($data->sticky ? " <span class=\"label\">置顶</span> " : "") . ($data->promote ? " <span class=\"label\">推荐至首页</strong> " : "")',
		),
		array(
			'name'=>'status',
			'class'=>'YStatusColumn',
			'statusList' => $model->getStatusList(),
			'labelMap' => array(
				Node::STATUS_DRAFT => 'warning',
				Node::STATUS_PUBLIC => 'success',
			),
			'filter'=>$model->getStatusList(),
		),
		/*
		array(
			'name'=>'hits',
			'type'=>'raw',
			'value'=>'"<span class=\"badge\">" . $data->hits . "</span>"',
			'headerHtmlOptions'=>array('class'=>'column-hits'),
		),
		*/
		array(
			'name'=>'create_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'filter'=>YUtil::timeFilterList(),
		),
		array(
			'class'=>'YContentButtonColumn',
			'channel'=>$channel,
		),
	),
)); ?>
<?php $this->endWidget(); ?>
