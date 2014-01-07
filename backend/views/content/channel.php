<?php
/* @var $this ChannelController */
/* @var $model Channel */
/* @var $form TbActiveForm */

if ($model->type == Channel::TYPE_PAGE) {
	$this->pageTitle = '更新' . $model->title;
} else {
	$this->pageTitle = $model->title;
}

$this->breadcrumbs=$this->generateBreadcrumb($model);
$this->breadcrumbs[]=$model->title;

$this->menu = $this->getContentItems($model->id);

?>

<div class="page-header">
	<h1><i class="icon-<?php echo $model->type == Channel::TYPE_PAGE ? 'pencil' : 'list'; ?>"></i> <?php echo $this->pageTitle; ?></h1>
	<?php if ($this->menu): ?>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 内容列表 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
	<?php endif;?>
</div>
<?php if ($model->type == Channel::TYPE_PAGE): ?>
<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'channel-form',
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php $this->widget('ext.ckeditor.CkeditorWidget', array(
		'form'=>$form,
		'model'=>$model,
		'attribute'=>'content',
	)); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
<?php else: ?>
<?php
$this->widget('bootstrap.widgets.TbExtendedGridView', array(
	'id'=>'channel-grid',
	'dataProvider'=>new CArrayDataProvider($model->getTree($model->id), array(
		'pagination'=>array(
			'pageSize'=>50,
		),
	)),
	//'rowHtmlOptionsExpression' => 'array("class"=>"level-" .$data->depth)',
	'columns'=>array(
		array(
			'name'=>'id',
			'header'=>$model->getAttributeLabel('id'),
			'headerHtmlOptions'=>array('class'=>'id-column'),
		),
		array(
			'name'=>'title',
			'type'=>'raw',
			'header'=>$model->getAttributeLabel('title'),
			'value'=>'"<span class=\"depth-indent\">" . str_repeat(" — ", $data->depth) . "</span>" . $data->title',
			'htmlOptions'=>array('class'=>'title-column'),
		),
		array(
			'name'=>'name',
			'header'=>$model->getAttributeLabel('name'),
		),
		array(
			'header'=>'操作',
			'type'=>'raw',
			'value'=>'$data->getContentActionLink()',
		),
	),
)); ?>
<?php endif; ?>