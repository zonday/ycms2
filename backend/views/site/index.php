<?php
/* @var $this SiteController */

$this->pageTitle = '控制面板';
$this->breadcrumbs = array(
	'控制面板'
);
?>
<div class="page-header">
	<h1><i class="icon-dashboard"></i> 控制面板</h1>
</div>

<div class="dashboard row-fluid">
	<div class="span5 column">
		<?php
		$this->beginWidget('bootstrap.widgets.TbBox', array(
			'title' => '欢迎',
		));
		?>
		<?php if ($user = User::model()->cache(60)->findByPk(Yii::app()->getUser()->getId())): ?>
		<p><strong><?php echo CHtml::encode($user->nickname); ?></strong></p>
		<p>最后登录时间：<?php echo date('Y-m-d H:i:s', $user->login_time); ?></p>
		<?php endif; ?>
		<?php $this->endWidget();?>
		<?php
		$this->beginWidget('bootstrap.widgets.TbBox', array(
			'title' => '服务器信息',
		));
		?>
		<ul>
			<li>服务器软件：<?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
			<li>操作系统：<?php echo defined('PHP_OS')?PHP_OS:'未知'; ?></li>
			<li>PHP版本：<?php echo @phpversion(); ?> </li>
			<li>MYSQL版本：<?php echo @mysql_get_server_info(); ?></li>
			<li>当前时间：<?php echo date("Y-m-d H:i:s"); ?> </li>
			<li>使用域名：<?php echo $_SERVER['HTTP_HOST']; ?> </li>
		</ul>
		<?php $this->endWidget();?>
	</div>
	<div class="span7 column">
		<?php
		$this->beginWidget('bootstrap.widgets.TbBox', array(
			'title' => '最近的',
		));
		?>
		<?php foreach (Channel::getModelList() as $modelClass => $name):
			$staticModel = CActiveRecord::model($modelClass);
			if (!$staticModel instanceof Node)
				continue;
		?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="title-column">标题</th>
					<th class="channel-column">栏目</th>
					<th class="datetime-column">发布时间</th>
					<th class="status-column">状态</th>
				</tr>
			</thead>
			<tbody>
					<?php foreach ($staticModel->cache(60)->findAll(array('select'=>'id, channel_id, title, create_time, status', 'limit'=>10, 'order'=>'update_time DESC')) as $model): ?>
					<?php $channel = Channel::get($model->channel_id); ?>
				<tr>
					<td class="title-column"><?php echo CHtml::link(CHtml::encode($model->title), array('content/update', 'channel'=>$model->channel_id, 'id'=>$model->id), array('title'=>CHtml::encode($model->title))); ?> </td>
					<td class="channel-column"><?php echo CHtml::link(CHtml::encode($channel->title), array('content/index', 'channel'=>$channel->id), array('title'=>'查看该栏目下的文章')); ?></td>
					<td class="datetime-column"><?php echo Yii::app()->format->date($model->create_time)?></td>
					<td class="status-column"><?php echo $model->statusList[$model->status]; ?></td>
				</tr>
					<?php endforeach; ?>
			</tbody>
		</table>
		<?php endforeach; ?>
		<?php $this->endWidget();?>
	</div>
</div>
