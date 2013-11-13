<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'term-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php //echo $form->errorSummary($model); ?>

	<?php
	if (isset($model->YFileUsageBehavior)) {
		$this->widget('YUploadWidget', array(
			'model' => $model,
			'attribute' => 'image',
			'form' => $form,
		));
	}
	?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>128)); ?>

	<?php echo $form->textFieldRow($model,'slug',array('class'=>'span5','maxlength'=>128,'hint'=>'唯一标识符，只能包含字母，数字和连字符（-）')); ?>

	<?php
	if ($model->taxonomy->hierarchy != Taxonomy::HIERARCHY_DISABLED)
	{
		$htmlOptions = array('class'=>'span2');
		if ($model->taxonomy->hierarchy == Taxonomy::HIERARCHY_MULTIPLE)
			$htmlOptions['multiple'] = true;
		$list = array('0'=>'根') + $model->generateTreeList();
		$htmlOptions['data'] = $list;
		echo $form->select2Row($model, 'parentIds', $htmlOptions);
		//echo $form->dropDownListRow($model, 'parentIds', $list, $htmlOptions);
	} else
		echo $form->hiddenField($model, 'parentIds', array('value'=>0));
	?>

	<?php echo $form->textAreaRow($model,'description',array('rows'=>6, 'cols'=>50, 'class'=>'span8')); ?>

	<?php echo $form->dropDownListRow($model,'weight',$model->getWeightList(),array('class'=>'span1', 'hint'=>'数值越大排名越后')); ?>

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
			'htmlOptions'=>array('class'=>'pull-right', 'csrf'=>true,'submit'=>array('delete','id'=>$model->id),'confirm'=>'删除这个术语将会导致该术语下的所有内容关系将被删除！请谨慎操作！'),
			'label'=>'<i class="icon-trash"></i> 删除',
			'visible' => !$model->isNewRecord,
		));
		?>
	</div>

<?php $this->endWidget(); ?>
