<?php
$this->pageTitle = '上传文件';

$this->breadcrumbs=array(
	'文件'=>array('index'),
	'上传',
);
?>

<div class="page-header">
	<h1><i class="icon-upload"></i> 上传文件</h1>
</div>

<ul class="nav nav-tabs">
	<li class="active"><a href="#upload-general" data-toggle="tab">标准上传</a></li>
	<li><a href="#upload-advanced" data-toggle="tab">高级上传</a></li>
</ul>
<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'file-upload-form',
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array(
		'enctype'=>'multipart/form-data',
	)
)); ?>
<?php echo $form->errorSummary($model); ?>
<div class="tab-content">
	<div class="tab-pane active" id="upload-general">
	<?php
		$hint = '允许上传的文件类型：' . $model->getFileTypesByField('file') . '。最大大小为：' . $model->getFileMaxSizeByField('file');
	?>

	<?php echo $form->fileFieldRow($model, 'file', array('hint'=>$hint)); ?>

	<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'上传',
		)); ?>
	</div>
	<div class="tab-pane" id="upload-advanced">
		<?php $this->widget('YUploadWidget', array(
			'model' => $model,
			'attribute' => 'files',
			'form' => $form,
		))?>
	</div>
</div>
<?php $this->endWidget(); ?>
