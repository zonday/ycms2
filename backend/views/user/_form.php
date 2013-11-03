<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'user-form',
	'type'=>'horizontal',
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php //echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'username',array('class'=>'span5','maxlength'=>64, 'hint'=>'只能包含字母和数字 、-、 _， 长度在6-16之间，且必须唯一')); ?>

	<?php echo $form->passwordFieldRow($model,'password',array('class'=>'span5','maxlength'=>128, 'hint'=>!$model->isNewRecord ? '留空则不更新' : '')); ?>

	<?php echo $form->passwordFieldRow($model,'password_repeat',array('class'=>'span5','maxlength'=>128)); ?>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>128, 'hint'=>'必须唯一')); ?>

	<?php echo $form->textFieldRow($model,'nickname',array('class'=>'span5','maxlength'=>64)); ?>

	<?php if ($allRoleList = Role::allList()): ?>
	<?php echo $form->checkBoxListRow($model,'roleNames', $allRoleList); ?>
	<?php endif; ?>

	<?php echo $form->radioButtonListRow($model,'status', $model->getStatusList()); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>$model->isNewRecord ? '创建' : '保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
