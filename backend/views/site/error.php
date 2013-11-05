<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle='Error';
?>

<div class="content error-content">
	<h1>Error <?php echo $code; ?></h1>

	<div class="error-message">
	<?php echo CHtml::encode($message); ?>
	</div>
	<div class="error-actions">
		<?php echo CHtml::link('<i class="icon-chevron-left icon-white"></i> 回到首页', array('site/index'), array('class'=>'btn btn-large btn-primary'))?>
		<?php echo CHtml::link('<i class="icon-envelope"></i> 联系管理员', 'mailto:' . Setting::get('system', 'admin_email'), array('class'=>'btn btn-large'))?>
	</div>
</div>