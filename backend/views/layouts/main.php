<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex,nofollow" />
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/style.css"/>
	</head>
	<body>
		<?php
			$this->widget('bootstrap.widgets.TbNavbar', array(
				'fluid'=>true,
				'type'=>'inverse',
				'collapse'=>true,
				'items' => array(
					! Yii::app()->getUser()->isGuest ?
					array(
						'class'=>'bootstrap.widgets.TbMenu',
						'htmlOptions'=>array(
							'class'=>'pull-right',
						),
						'items'=>array(
							array('label'=>'', 'icon'=>'bell', 'url'=>'#'),
							array('label'=>'欢迎！ '.CHtml::encode(Yii::app()->getUser()->nickname), 'url'=>'#', 'items'=>array(
								array('label'=>'个人资料', 'url'=>array('/user/profile'), 'icon'=>'user'),
								array('divider'=>true),
								array('label'=>'退出', 'url'=>array('/site/logout'), 'icon'=>'off'),
							)),
						),
					) : null,
				)
			));
		?>
		<div class="main-container" id="main-container">
			<div class="main-container-inner">
				<div class="sidebar" id="sidebar">
				<?php
				$this->widget(
					'bootstrap.widgets.TbMenu',
					array(
						'type' => 'list',
						'items' => array(
							array('label' => '首页', 'url' => array('/site/index'), 'icon' => 'home'),
							array('label' => '系统', 'itemOptions' => array('class' => 'nav-header')),
							array('label' => '用户', 'url' => array('/user/index'), 'icon' => 'user'),
							array('label' => '角色', 'url' => array('/role/index'), 'icon' => 'group'),
							array('label' => '权限', 'url' => array('/permission/index'), 'icon' => 'lock'),
							array('label' => '设置', 'url' => array('/site/setting'), 'icon' => 'cog'),
						)
					)
				);
				?>
				</div> <!-- /.sidebar -->
				<div class="main-content">
					<div class="breadcrumbs">
						<?php if (isset($this->breadcrumbs)): ?>
							<?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
								'links' => $this->breadcrumbs,
							)); ?><!-- breadcrumbs -->
						<?php endif?>
					</div>
					<div class="page-content">
						<?php $this->widget('bootstrap.widgets.TbAlert'); ?>
						<?php echo $content; ?>
					</div> <!-- /.page-content -->
				</div> <!-- /.main-content -->
			</div> <!-- /.main-container-inner -->
		</div> <!-- /.main-container -->
	</body>
</html>
