<?php
/* @var $this TaxonomyController */
$this->pageTitle = '查看分类';

$this->breadcrumbs = array(
	'分类'=>array('index'),
	$model->name,
);

$this->menu = array(
	array('label'=>'分类列表','url'=>array('index'), 'icon'=>'list'),
	array('label'=>'创建分类','url'=>array('create'), 'icon'=>'plus'),
	array('label'=>'更新分类','url'=>array('update', 'id'=>$model->id), 'icon'=>'pencil'),
	array('label'=>'删除分类','url'=>'#', 'icon'=>'trash', 'linkOptions'=>array('csrf'=>true, 'submit'=>array('delete','id'=>$model->id),'confirm'=>'删除这个分类将导致该分类下的所有术语将被删除！且术语下的内容关系也将被删除，请谨慎操作！')),
);
?>

<div class="page-header">
	<h1><i class="icon-eye-open"></i> 查看分类</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'slug',
		array(
			'name'=>'hierarchy',
			'value'=>$model->hierarchyList[$model->hierarchy],
		),
		'description',
		'weight',
	),
)); ?>

<div class="pull-right">
	<?php echo CHtml::link('创建术语', array('term/create', 'taxonomy_id'=>$model->id), array('class'=>'btn btn-primary')); ?>
</div>

<h2>术语列表</h2>
<?php /* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'action' => array('term/saveWeight', 'taxonomy_id'=>$model->id),
));
?>
<?php
$term = Term::model();
$term->taxonomy = $model;
$term->taxonomy_id = $model->id;
$this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'term-grid',
	'dataProvider'=>new CArrayDataProvider($model->getTree(), array(
		'sort' => array(
			'attributes' => array('id', 'name', 'slug', 'weight'),
		)
	)),
	//'filter'=>null,
	'enableSorting'=> $model->hierarchy == Taxonomy::HIERARCHY_DISABLED ? true : false,
	'columns'=>array(
		array(
			'name'=>'id',
			'header'=>$term->getAttributeLabel('id'),
			'headerHtmlOptions'=>array('class'=>'id-column'),
		),
		array(
			'name'=>'name',
			'header'=>$term->getAttributeLabel('name'),
			'headerHtmlOptions'=>array('class'=>'name-column'),
			'type'=>'raw',
			'value'=>'"<span class=\"depth-indent\">" . str_repeat(" — ", $data->depth) . "</span>" . CHtml::encode($data->name)',
		),
		array(
			'name'=>'slug',
			'header'=>$term->getAttributeLabel('slug'),
			'headerHtmlOptions'=>array('class'=>'slug-column'),
		),
		array(
			'name'=>'description',
			'header'=>$term->getAttributeLabel('description'),
			'headerHtmlOptions'=>array('class'=>'description-column'),
		),
		array(
			'class'=>'YInputColumn',
			'name'=>'weight',
			'header'=>$term->getAttributeLabel('weight'),
			'headerHtmlOptions'=>array('class'=>'input-column'),
			'inputHtmlOptions'=>array('name'=>'weight')
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'deleteConfirmation' => "删除这个术语将会导致该术语下的所有内容关系将被删除！请谨慎操作！",
			'buttons'=>array(
				'view' => array(
					'url' => 'Yii::app()->createUrl("/term/view",array("id"=>$data->id))'
				),
				'update' => array(
					'url' => 'Yii::app()->createUrl("/term/update",array("id"=>$data->id))'
				),
				'delete' => array(
					'url' => 'Yii::app()->createUrl("/term/delete",array("id"=>$data->id))'
				),
			)
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