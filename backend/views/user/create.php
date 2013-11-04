<?php
/* @var $this UserController */

$this->pageTitle = '创建用户';
$this->breadcrumbs = array(
	'用户' => array('index'),
	'创建用户'
);
?>
<div class="page-header">
	<h1><i class="icon-plus"></i> 创建用户</h1>
</div>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>