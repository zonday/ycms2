<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'role-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php //echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>64, 'hint'=>'唯一标识符，只能包含小写英文字母。')); ?>

	<?php echo $form->textFieldRow($model,'description',array('class'=>'span5','maxlength'=>128 )); ?>

	<?php echo $form->dropDownListRow($model,'weight', $model->getWeightList(), array('class'=>'span1', 'hint'=>'权重从小到大排序')); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'encodeLabel'=>false,
			'label'=>$model->isNewRecord ? '<i class="icon-plus"></i> 创建' : '<i class="icon-save"></i> 保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
