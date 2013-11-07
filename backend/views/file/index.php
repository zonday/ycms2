<?php
/* @var $this FileController */

$this->pageTitle = '文件列表';

$this->breadcrumbs=array(
	'文件',
);

$this->menu=array(
	array('label'=>'上传文件','url'=>array('upload'), 'icon'=>'upload'),
	array('label'=>'清理文件','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true,'submit'=>array('clean'),'confirm'=>'该操作将会删除系统上未被使用的文件（已经上传至服务器上的），确定删除吗?'),),
);

?>
<div class="page-header">
	<h1><i class="icon-file"></i> 文件</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php
if (isset($model->user_id)) {
	echo '<p>上传者：' . ($model->user ? CHtml::encode($model->user->nickname) : '匿名用户') . CHtml::link('<i class="icon-remove-sign"></i>', array('index')). '</p>';
	$model->user_id = $model->user ? $model->user->username : '匿名用户';
}
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
	'dataProvider'=>$model->with('user')->search(),
	'filter'=>$model,
	//'fixedHeader' => true,
	//'headerOffset' => 40,
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
			'name'=>'icon',
			'header'=>'',
			'value'=>'$data->getIcon()',
			'filter'=>false,
			'type'=>'raw',
			'headerHtmlOptions'=>array('class'=>'file-icon-column'),
			'htmlOptions'=>array('class'=>'file-icon-column'),
		),
		array(
			'name'=>'filename',
			'header'=>$model->getAttributeLabel('name'),
			'type'=>'raw',
			'value'=>'Chtml::encode($data->name) . " <br /><strong>" . strtoupper($data->ext) . "</strong>"',
		),
		array(
			'name'=>'user_id',
			'value'=>'CHtml::link($data->user ? CHtml::encode($data->user->nickname) : "匿名用户", array("index", "File[user_id]" =>$data->user_id ))',
			'type'=>'raw',
			'headerHtmlOptions'=>array('class'=>'username-column'),
			'htmlOptions'=>array('class'=>'username-column'),
		),
		array(
			'name'=>'create_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'filter'=>YUtil::timeFilterList(),
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
		),
	),
));
?>
<?php $this->endWidget(); ?>
