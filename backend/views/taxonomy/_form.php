<?php
/* @var $form TbActiveForm */
$form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'taxonomy-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php //echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>128)); ?>

	<?php echo $form->textFieldRow($model,'slug',array('class'=>'span5','maxlength'=>128, 'hint'=>'“别名”是在 URL 中使用的别称，它可以令 URL 更美观。通常使用小写，只能包含字母，数字和连字符（-）。')); ?>

	<?php echo $form->dropDownListRow($model, 'hierarchy', $model->getHierarchyList(), array('class'=>'span2')); ?>

	<?php echo $form->textAreaRow($model,'description',array('rows'=>6, 'cols'=>50, 'class'=>'span8')); ?>

	<?php echo $form->dropDownListRow($model,'weight', $model->getWeightList(), array('class'=>'span1', 'hint'=>'数值越大排名越后')); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'htmlOptions'=>array('name'=>'_save'),
			'type'=>'primary',
			'encodeLabel'=>false,
			'label'=>$model->isNewRecord ? '<i class="icon-plus"></i> 创建' : '<i class="icon-save"></i> 保存',
		)); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'htmlOptions'=>array('name'=>'_addanother'),
			'label'=>$model->isNewRecord ? '创建并增加另一个' : '保存并增加另一个',
		)); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'htmlOptions'=>array('name'=>'_continue'),
			'label'=>$model->isNewRecord ? '创建并继续更新' : '保存并继续更新',
		)); ?>
		<?php
		$this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submitLink',
			'type'=>'danger',
			'encodeLabel'=>false,
			'htmlOptions'=>array('class'=>'pull-right', 'csrf'=>true,'submit'=>array('delete','id'=>$model->id),'confirm'=>'确定要删除这条数据吗?'),
			'label'=>'<i class="icon-trash"></i> 删除',
			'visible' => !$model->isNewRecord,
		));
		?>
	</div>

<?php $this->endWidget(); ?>
