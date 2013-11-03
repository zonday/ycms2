<?php
/* @var $this SiteController */

$this->pageTitle = '用户列表';
$this->breadcrumbs = array(
	'用户'
);
?>
<div class="page-header clearfix">
	<h1>用户</h1>
	<div class="actions">
		<?php echo CHtml::link('创建用户', array('user/create'), array('class'=>'btn btn-primary')); ?>
	</div>
</div>