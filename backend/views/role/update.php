<?php
/* @var $this RoleController */

$this->pageTitle='创建角色';

$this->breadcrumbs=array(
	'用户' => array('user/index'),
	'角色' => array('index'),
	'更新'
);

?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> 更新角色</h1>
</div>

<?php $this->renderPartial('_form', array('model' => $model)); ?>
