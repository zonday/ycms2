<?php
/* @var $this UserController */

$this->pageTitle = '用户列表';
$this->breadcrumbs = array(
	'用户'
);
?>
<div class="page-header">
	<h1><i class="icon-user"></i> 用户</h1>
	<div class="pull-right">
		<?php echo CHtml::link('<i class="icon-plus icon-white"></i> 创建用户', array('create'), array('class'=>'btn btn-primary')); ?>
	</div>
</div>

<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'type'=>'horizontal',
));
$actions = array(
	'' => '批量操作',
	//'active' => '激活验证选中用户',
	//'notactive' => '不激活验证选中用户',
	'block' => '禁止选中用户',
	'unblock' => '解禁选中用户',
	//'delete' => '删除选中用户',
);

$allRoleList = Role::allList();
$addKey = '为选中的用户添加角色';
$removeKey = '为选中的用户删除角色';

foreach ($allRoleList as $name => $description) {
	$actions[$addKey]['add_role-' . $name] = $description;
	$actions[$removeKey]['remove_role-' . $name] = $description;
}

?>
<?php $this->widget('bootstrap.widgets.TbExtendedGridView',array(
	'id'=>'user-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'bulkActions' => array(
		'class' => 'YBulkActions',
		'align' => 'left',
		'actionButtons' => $actions,
	),
	'columns'=>array(
		array(
			'name'=>'id',
			'class'=>'CCheckBoxColumn',
			'selectableRows'=>2,
			'checkBoxHtmlOptions'=>array('name' => 'ids[]'),
		),
		'username',
		'nickname',
		'email',
		array(
			'name'=>'roleNames',
			'value'=>'implode(", ", $data->roleList)',
			'filter' => $allRoleList,
		),
		array(
			'name'=>'status',
			'class'=>'YStatusColumn',
			'statusList' => $model->getStatusList(),
			'labelMap' => array(
				User::STATUS_DEFAULT => 'success',
				User::STATUS_NOT_ACTIVATED => 'warning',
				User::STATUS_BLOCK => 'important',
			),
			'filter'=>$model->getStatusList(),
		),
		array(
			'name'=>'create_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'filter'=>YUtil::timeFilterList(),
		),
		array(
			'name'=>'login_time',
			'class'=>'YDatetimeColumn',
			'type'=>'date',
			'nullDisplay' => '从未登录',
			'filter'=> array('0' => '从未登录') + YUtil::timeFilterList(),
		),
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
			'template'=>'{view} {update} {cannel}',
			'buttons' => array(
				'cannel' => array(
					'label'=>'删除',
					'icon'=>'trash',
					'url'=>'Yii::app()->controller->createUrl("cannel", array("id"=>$data->id))',
					'visible' => '$data->id != User::SUPERADMIN_ID',
				)
			)
		),
	),
)); ?>
<?php $this->endWidget(); ?>
