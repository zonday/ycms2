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
			'title' => '最近的内容',
		));
		?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="title-column">标题</th>
					<th class="channel-column">栏目</th>
					<th class="datetime-column">更新时间</th>
					<th class="status-column">状态</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$statusLabelMap = array(
					Node::STATUS_DRAFT => 'warning',
					Node::STATUS_PUBLIC => 'success',
					Node::STATUS_TRASH => 'inverse',
				);
				foreach (Channel::getModelList() as $modelClass => $name):
					$staticModel = CActiveRecord::model($modelClass);
					if (!$staticModel instanceof Node)
						continue;

					$staticModel->byChannel(Channel::getChannelsByModel($modelClass));
				?>
				<tr><th colspan="4" align="center"><?php echo Channel::model()->getModelName($modelClass); ?></th></tr>
					<?php
					$select = 'id, title, update_time, status';
					if ($staticModel->hasAttribute('channel_id')) {
						$select .= ', channel_id';
					}
					foreach ($staticModel->cache(60)->findAll(array('select'=>$select, 'limit'=>5, 'order'=>'update_time DESC')) as $model): ?>
					<?php
						$channel = $model->getChannel();
					?>
				<tr>
					<td class="title-column"><?php echo CHtml::link(CHtml::encode($model->title), array('content/update', 'channel'=>$channel->id, 'id'=>$model->id), array('title'=>CHtml::encode($model->title))); ?> </td>
					<td class="channel-column"><?php echo CHtml::link(CHtml::encode($channel->title), array('content/index', 'channel'=>$channel->id), array('title'=>'查看该栏目下的文章')); ?></td>
					<td class="datetime-column"><abbr title="<?php echo Yii::app()->format->datetime($model->update_time); ?>"><?php echo Yii::app()->format->date($model->update_time)?></abbr></td>
					<td class="status-column"><span class="label label-<?php echo $statusLabelMap[$model->status]?>"><?php echo $model->statusList[$model->status]; ?></span></td>
				</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php $this->endWidget();?>
	</div>
</div>
