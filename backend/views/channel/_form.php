<?php
/* @var $this ChannelController */
/* @var $model Channel */
/* @var $form TbActiveForm */
?>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'channel-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'title',array('class'=>'span5','maxlength'=>64)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>64, 'hint'=>'唯一标识符，只能包含英文和数字、-')); ?>

	<?php echo $form->dropDownListRow($model,'type', $model->getTypeList(), array('hint'=>'栏目类型')); ?>

	<?php echo $form->dropDownListRow($model,'model',$model->getModelList(),array('empty'=>'无')); ?>

	<?php echo $form->dropDownListRow($model,'parent_id', $model->getTreeList(0), array('empty'=>'无')); ?>

	<?php echo $form->dropDownListRow($model,'weight', $model->getWeightList(), array('class'=>'span1', 'hint'=>'数值越大排名越后')); ?>

	<?php echo $form->textFieldRow($model,'keywords',array('class'=>'span5','maxlength'=>64, 'hint'=>'关键字用英文逗号或者空格分隔开来')); ?>

	<?php echo $form->textareaRow($model,'description',array('class'=>'span5', 'rows'=>5)); ?>

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
			'htmlOptions'=>array('class'=>'pull-right', 'csrf'=>true,'submit'=>array('delete','id'=>$model->id)),
			'label'=>'<i class="icon-trash"></i> 移动至回收站',
			'visible' => !$model->isNewRecord,
		));
		?>
	</div>

<?php $this->endWidget(); ?>