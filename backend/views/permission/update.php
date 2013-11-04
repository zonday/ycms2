<?php
/* @var $this PermissionController */

$this->pageTitle='更新' . $model->typeNames[$type];
$this->breadcrumbs=array(
	'用户' => array('user/index'),
	'权限' => array('index'),
	'更新' . $model->typeNames[$type],
);
?>

<div class="page-header">
	<h1><i class="icon-pencil"></i> <?php echo $this->pageTitle; ?></h1>
</div>

<?php $this->renderPartial('_form', array('model' => $model)); ?>
