<?php
/* @var $this RoleController
 * @var $form TbActiveForm
 */
$this->pageTitle='角色列表';

$this->breadcrumbs=array(
	'用户' => array('user/index'),
	'角色',
);
?>

<div class="page-header">
	<h1>角色</h1>
	<div class="actions">
		<?php echo CHtml::link('<i class="icon-plus icon-white"></i> 创建角色', array('create'), array('class'=>'btn btn-primary')); ?>
	</div>
</div>

<?php
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'action' => array('saveWeight'),
));
?>
<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'taxonomy-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'name',
		'description',
		array(
			'class'=>'YInputColumn',
			'name'=>'weight',
			'primaryKey' => 'name',
			'inputHtmlOptions' => array('name'=>'weight')
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'deleteConfirmation' => '确定要删除这个角色吗？该操作不可逆转，请谨慎操作',
			'template' => '{update} {delete}',
		),
	),
)); ?>
<div class="text-right">
<?php $this->widget('bootstrap.widgets.TbButton', array(
	'buttonType'=>'submit',
	'htmlOptions'=>array('name'=>'saveWeight'),
	'type'=>'primary',
	'label'=>'保存权重',
));
?>
</div>
<?php $this->endWidget(); ?>