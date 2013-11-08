<?php
$this->pageTitle = '删除用户';

$this->breadcrumbs=array(
	$model->username => array('view', 'id'=>$model->id),
	'删除用户',
);
?>

<div class="page-header">
	<h1><i class="icon-trash"></i> 删除用户</h1>
</div>

<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'user-form',
)); ?>

	<p class="help-block">确定要删除 <strong><?php echo $model->username; ?></strong> 吗？</p>

	<div class="radio inline">
	<?php
		$data = array(
			'block'=>'禁用帐户，并保留其所有内容',
			'block_unpublish'=>'禁用此账户并撤下其所有内容',
			'reassign'=>'删除这个帐号，把此帐号所有的内容转到匿名用户下',
			'delete'=>'删除该账号及其内容',
		);

		echo CHtml::radioButtonList('method', 'block', $data);
	?>
	</div>
	<div class="alert">选择上面删除这个账户的方法。 这个操作不可恢复。</div>
	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>'确定',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
