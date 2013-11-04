<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'file-form',
	'type'=>'horizontal',
)); ?>
	<div class="row-fluid">
		<div class="<?php echo $isAjax ? "span12" : "span8" ?> media-fields" id="media-fields">
			<p class="help-block"><span class="required">*</span> 字段必填.</p>

			<?php if (!$isAjax): ?>
			<?php echo $form->errorSummary($model); ?>
			<?php endif;?>

			<?php echo $form->textFieldRow($model,'name',array('class'=>'span12','maxlength'=>64)); ?>

			<?php
			if ($model->isImage())
				echo $form->textFieldRow($model, 'alt',array('class'=>'span12','maxlength'=>128));
			?>

			<?php echo $form->textareaRow($model,'caption',array('class'=>'span12','rows'=>2)); ?>

			<?php echo $form->textareaRow($model,'description',array('class'=>'span12','rows'=>2)); ?>
		</div>
		<?php if (!$isAjax): ?>
		<div class="span4 media-info" id="media-info">
			<div class="media-icon">
			<?php echo $model->getIcon(array('width'=>150, 'height'=>150)); ?>
			</div>
			<div class="caption">
				<ul>
					<li><strong class="label">大小</strong><?php echo Yii::app()->format->size($model->size); ?></li>
					<li><strong class="label">Mime</strong><?php echo $model->filemime; ?></li>
					<li><strong class="label">Url</strong><?php echo CHtml::textField('url', $model->getUrl(), array('readonly'=>true, 'class'=>'clone')); ?></li>
					<li><strong class="label">上传时间</strong><?php echo Yii::app()->format->datetime($model->create_time); ?>
				</ul>
			</div>
		</div>
		<?php endif;?>
	</div>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'encodeLabel'=>false,
			'label'=>$model->isNewRecord ? '<i class="icon-plus"></i> 创建' : '<i class="icon-save"></i> 保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
