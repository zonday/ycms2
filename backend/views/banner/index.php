<?php
/* @var $this BannerController */

$this->pageTitle = 'Banner列表';

$this->breadcrumbs=array(
	'Banner',
);
?>

<div class="page-header">
	<h1><i class="icon-picture"></i> Banner</h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus"></i> 创建Banner', array('create'), array('class' => 'btn btn-primary')); ?>
	</div>
	<?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
		'htmlOptions' => array('class'=>'pull-right'),
		'buttons'=>array(
			array('icon'=>'icon-th', 'url'=>array('index', 'view'=>'grid'), 'active'=>$view == 'grid' ? true : false),
			array('icon'=>'icon-th-list', 'url'=>array('index', 'view'=>'list'), 'active'=>$view == 'list' ? true : false),
		)
	)); ?>
</div>

<?php
if ($view == 'grid'):
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'type'=>'horizontal',
));
$actions = array(
	'' => '批量操作',
	'delete' => '删除',
);
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
		array(
			'name'=>'icon',
			'header'=>'',
			'value'=>'$data->imageFile ? $data->imageFile->getIcon() : null',
			'filter'=>false,
			'type'=>'raw',
			'headerHtmlOptions'=>array('class'=>'file-icon-column'),
			'htmlOptions'=>array('class'=>'file-icon-column'),
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
			'toggleAction' => 'banner/toggle',
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
<?php
elseif ($view == 'list'):
$this->widget('bootstrap.widgets.TbThumbnails',array(
	'id'=>'file-list',
	'dataProvider'=>$model->search(),
	'itemView'=>'_view',
));
else:
$items = array();
foreach ($model->search()->getData() as $data) {
	$items[] = array(
		'image' => $data->getImageFile()->getUrl(),
		'label' => $data->name,
		'caption' => isset($data->description) ? $data->description : 'test',
	);
}
$this->widget('bootstrap.widgets.TbCarousel',
	array(
		'items' =>$items,
		'htmlOptions' => array('style'=>'max-width: 800px'),
	)
);
endif;
?>
