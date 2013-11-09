<?php
/* @var $this SiteController */
/* @var $model ResetPasswordForm */
/* @var $form CActiveForm  */
$this->pageTitle='重置密码';
?>

<div class="content signin-content">

<?php $this->widget('bootstrap.widgets.TbAlert'); ?>
<h1>重置密码</h1>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'reset-password-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->hiddenField($model, 'login') ?>
	<?php echo $form->hiddenField($model, 'key'); ?>

	<?php echo $form->passwordFieldRow($model,'password', array('class'=>'input-block-level')); ?>

	<?php echo $form->passwordFieldRow($model,'password_repeat', array('class'=>'input-block-level')); ?>

	<div class="text-right">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'确定',
		)); ?>
	</div>
	<?php echo Chtml::link('&larr;返回登录', array('login'))?>
<?php $this->endWidget(); ?>

</div>