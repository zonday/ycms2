<?php
/* @var $this SettingController */
/* @var $model Setting */
/* @var $form TbActiveForm */
$this->pageTitle = $categories[$category];

$this->breadcrumbs=array(
	'设置' => array('index'),
	$categories[$category]
);
foreach ($categories as $category => $description)
	$this->menu[] = array('label' => $description, 'url' => array('setting', 'category' => $category));

$this->menu[] = array('label'=>'清空缓存', 'url'=>array('flushCache'));
?>
<div class="page-header">
	<h1><i class="icon-cog"></i>设置</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"> 操作 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => $this->menu,
		))?>
	</div>
</div>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'setting-form',
	'type'=>'horizontal',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block"><span class="required">*</span> 字段必填.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php foreach ($model->getOptions() as $name => $params):
		$type = isset($params['type']) ? $params['type'] : 'text';
		$hint = isset($params['hint']) ? $params['hint']: null;
		switch ($type) {
			case 'textarea':
				echo $form->textAreaRow($model, $name, array('class'=>'span6', 'rows'=>5, 'hint'=>$hint));
				break;
			case 'bool':
				echo $form->radioButtonListRow($model, $name, array(0=>'否', 1=>'是', 'hint'=>$hint));
				break;
			default:
				echo $form->textFieldRow($model, $name, array('class'=>'span5', 'maxlength'=>255, 'hint'=>$hint));
		}
	?>
	<?php endforeach; ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'保存',
		)); ?>
	</div>

<?php $this->endWidget(); ?>