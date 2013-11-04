<?php
/* @var $this RoleController
 */
$this->pageTitle='权限列表';

$this->breadcrumbs=array(
	'用户' => array('user/index'),
	'权限',
);
?>

<div class="page-header">
	<h1><i class="icon-lock"></i> 权限</h1>
	<div class="btn-group pull-right">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><i class="icon-plus icon-white"></i> 创建 <span class="caret"></span></button>
		<?php $this->widget('bootstrap.widgets.TbDropdown', array(
			'items' => array(
				array('label'=>'创建任务', 'url' => array('create', 'type' => CAuthItem::TYPE_TASK)),
				array('label'=>'创建操作', 'url' => array('create', 'type' => CAuthItem::TYPE_OPERATION))
			)
		))?>
	</div>
</div>

<div class="grid-view">
<?php if (empty($data)): ?>
<p>还没有权限，您可以
<?php
echo CHtml::link('创建任务', array('create', 'type'=>CAuthItem::TYPE_TASK));
?>
 或
<?php
echo CHtml::link('创建操作', array('create', 'type'=>CAuthItem::TYPE_OPERATION));
?>。
</p>
<?php else: ?>
<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array('action'=>array('save'))); ?>
<?php foreach ($roleList as $roleName => $description) : ?>
<?php echo CHtml::hiddenField("auth[$roleName][]"); ?>
<?php endforeach; ?>
<table class="table">
	<thead>
		<tr>
			<th>权限</th>
			<th></th>
			<?php foreach ($roleList as $roleName => $description) : ?>
			<th><?php echo CHtml::encode($description); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data as $row):
			$buttons = array(
				array('label'=>'编辑', 'url'=>array('update', 'name'=>$row['name'])),
				array('label'=>'删除', 'url'=>'#','linkOptions'=>array('csrf'=>true,'submit'=>array('delete','name'=>$row['name']),'confirm'=>'确定要删除这条数据吗?')),
			)
		?>
		<tr>
			<td>
			<p>
				<?php if (isset($childNames[$row['name']])): ?>
					<?php echo ' — '; $buttons[] = array('label' => '移除', 'url' => array('remove', 'name'=>$row['name'], 'parent'=>$childNames[$row['name']]), 'linkOptions'=>array('rel' => 'tooltip', 'title' => '从上级任务中移除这个操作')); ?>
				<?php endif; ?>
				<?php echo CHtml::encode($row['authItem']->getDescription()) ?>
				<?php
				if ($row['authItem']->getType() == CAuthItem::TYPE_TASK){
					echo '<small class="label">任务</small>';
				} else {
					echo '<small class="label">操作</small>';
				}
				?>
			</p>
			</td>
			<td>
			<div class="btn-group">
				<button data-toggle="dropdown" class="btn dropdown-toggle">操作 <span class="caret"></span></button>
				<?php $this->widget('bootstrap.widgets.TbDropdown', array('items' => $buttons))?>
			</div>
			</td>
			<?php foreach ($roleList as $roleName => $description) : ?>
			<td>
				<?php echo CHtml::checkBox("auth[$roleName][]", empty($row['roles'][$roleName]) ? false : true, array('value' => $row['authItem']->getName())); ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<?php if ($roleList): ?>
<div class="text-right">
	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'buttonType'=>'submit',
		'type'=>'primary',
		'label'=>'保存权限',
	)); ?>
</div>
<?php endif; ?>
<?php $this->endWidget()?>
<?php endif; ?>
</div>
