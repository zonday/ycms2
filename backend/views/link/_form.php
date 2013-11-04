<?php
/* @var $this LinkController */
/* @var $model Link */
/* @var $form TbActiveForm */
?>
<div class="form">

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'history-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>64)); ?>

	<?php echo $form->textFieldRow($model,'link_href',array('class'=>'span5','maxlength'=>64)); ?>

	<?php echo $form->dropDownListRow($model, 'link_target', $model->getTargetList()); ?>

	<?php echo $form->dropDownListRow($model,'category_id', $model->getCategoryList()); ?>

	<?php echo $form->radioButtonListInlineRow($model, 'visible', array('0'=>'否', '1'=>'是')); ?>

	<?php echo $form->textAreaRow($model, 'description', array('class'=>'span5', 'rows'=>8)); ?>

	<?php echo $form->textFieldRow($model,'weight',array('class'=>'span1', 'hint'=>'按权重从小到大排序')); ?>

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
</div>
