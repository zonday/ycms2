<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle='登录';
?>
<div class="content signin-content">

<?php $this->widget('bootstrap.widgets.TbAlert'); ?>

<h1>登录</h1>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textFieldRow($model, 'username', array('class' => 'input-block-level')); ?>

	<?php echo $form->passwordFieldRow($model, 'password', array('class' => 'input-block-level')); ?>

	<?php if(CCaptcha::checkRequirements()): ?>
		<?php echo $form->captchaRow($model, 'verifyCode', array('class' => 'span2')); ?>
	<?php endif; ?>

	<?php echo $form->checkBoxRow($model,'rememberMe'); ?>

	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'buttonType' => 'submit',
		'type' => 'primary',
		'label' => '登录',
		'htmlOptions' => array('class' => 'btn-block'),
	)); ?>

	<p class="signin-actions"><?php echo CHtml::link('忘记密码了？', array('lostpassword'))?></p>

<?php $this->endWidget(); ?>

</div>
