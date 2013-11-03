<?php
/* @var $this SiteController */
/* @var $model LostPasswordForm */
/* @var $form CActiveForm  */

$this->pageTitle='忘记密码';
?>

<div class="content signin-content">

<?php $this->widget('bootstrap.widgets.TbAlert'); ?>

<h1>忘记密码</h1>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'lost-password-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textFieldRow($model, 'login', array('class'=>'input-block-level')); ?>

	<?php if(CCaptcha::checkRequirements()): ?>
		<?php echo $form->captchaRow($model, 'verifyCode', array('class'=>'span2')); ?>
	<?php endif; ?>

	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'buttonType'=>'submit',
		'type'=>'primary',
		'label'=>'确定',
		'htmlOptions' => array('class' => 'btn-block'),
	)); ?>

	<p class="signin-actions"><?php echo Chtml::link('&larr;返回登录', array('login'))?></p>

<?php $this->endWidget(); ?>
</div>