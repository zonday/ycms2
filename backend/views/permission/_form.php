<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'role-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php //echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>64, 'hint'=>'唯一标识符，请不要随便更改')); ?>

	<?php echo $form->textFieldRow($model,'description',array('class'=>'span5','maxlength'=>128, 'hint'=>'对这个授权项的描述')); ?>

	<?php echo $form->textFieldRow($model,'bizRule',array('class'=>'span5','maxlength'=>128, 'hint'=>'访问检查时这些代码将被执行')); ?>

	<?php
		if ($model->getType() == CAuthItem::TYPE_OPERATION):
			echo $form->dropDownListRow($model, 'task', array('0'=>'无') + $model->getTaskList());
		endif;
	?>
	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'encodeLabel'=>false,
			'label'=>$model->isNewRecord ? '<i class="icon-plus"></i> 创建' : '<i class="icon-save"></i> 保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
