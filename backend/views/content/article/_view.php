<?php $this->widget('bootstrap.widgets.TbDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(
			'name'=>'user_id',
			'value'=>$model->user ? $model->user->nickname : User::ANONYMOUS_NAME,
		),
		'title',
		array(
			'name'=>'excerpt',
		),
		array(
			'name'=>'content',
			'type'=>'raw',
		),
		array(
			'name'=>'create_time',
			'type'=>'datetime',
		),
		array(
			'name'=>'update_time',
			'type'=>'datetime',
		),
		array(
			'name'=>'sticky',
			'type'=>'boolean',
		),
		array(
			'name'=>'promote',
			'type'=>'boolean',
		),
		array(
			'name'=>'status',
			'value'=>$model->statusList[$model->status],
		),
	),
)); ?>