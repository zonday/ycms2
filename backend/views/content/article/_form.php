<?php
/* @var $this ContentController */
/* @var $model Article */
/* @var $form TbActiveForm */
?>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>get_class($model) . '-form',
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row-fluid">
		<div class="span8">
			<?php echo $form->textFieldRow($model,'title',array('class'=>'input-block-level','maxlength'=>128)); ?>

			<?php $this->widget('ext.ckeditor.CkeditorWidget', array(
				'model'=>$model,
				'form'=>$form,
				'attribute'=>'content',
			)); ?>
			<?php echo $form->textAreaRow($model,'excerpt',array('class'=>'input-block-level','rows'=>6)); ?>
		</div>
		<div class="span4">
			<div id="form-side">
				<?php
				$this->widget('YUploadWidget', array(
					'model' => $model,
					'attribute' => 'image',
					'form' => $form,
				))
				?>

				<?php
				if (isset($model->YTaxonomyBehavior) && $model->taxonomies()):
					$this->widget('YTaxonomyWidget', array(
						'model'=>$model,
						'form'=>$form,
					));
				endif;
				?>

				<?php echo $form->dropDownListRow($model, 'status', $model->getStatusList()); ?>

				<?php echo $form->checkBoxRow($model, 'sticky'); ?>

				<?php echo $form->checkBoxRow($model, 'promote'); ?>

				<?php $this->widget('ext.timepicker.timepicker', array(
					'model'=>$model,
					'form'=>$form,
					'language'=>'zh',
					'options'=>array(
						'showAnim'=>'fold',
						'dateFormat'=>'yy-mm-dd',
					),
					'name'=>'create_time',
				));?>
			</div>
		</div>
	</div>

	<div class="form-actions" data-editor="Article_content">
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
			'htmlOptions'=>array('class'=>'pull-right', 'csrf'=>true,'submit'=>array('delete', 'channel'=>$channel->id, 'id'=>$model->id),'confirm'=>'确定要删除这条数据吗?'),
			'label'=>'<i class="icon-trash"></i> 删除',
			'visible' => !$model->isNewRecord,
		));
		?>
	</div>
<?php $this->endWidget(); ?>
