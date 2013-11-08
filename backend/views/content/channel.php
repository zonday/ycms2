<?php
/* @var $this ChannelController */
/* @var $model Channel */
/* @var $form TbActiveForm */

$this->pageTitle = '更新' . $model->title;

$this->breadcrumbs=$this->generateBreadcrumb($model);
$this->breadcrumbs[]=$model->title;

$this->menu = $this->getContentItems($model->id);

?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> <?php echo $this->pageTitle; ?></h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 内容列表 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

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