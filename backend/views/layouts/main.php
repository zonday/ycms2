<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex,nofollow" />
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->baseUrl; ?>/css/style.css"/>
		<script type="text/javascript" src="<?php echo Yii::app()->baseUrl; ?>/js/common.js"></script>
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
								array('label'=>'退出', 'url'=>array('/site/logout'), 'icon'=>'fixed-width off'),
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
							array('label' => '首页', 'url' => array('/site/index'), 'icon' => 'fixed-width home'),
							array('label' => '系统', 'itemOptions' => array('class' => 'nav-header')),
							array('label' => '链接', 'url' => array('/link/index'), 'icon' => 'fixed-width link'),
							array('label' => 'Banner', 'url' => array('/banner/index'), 'icon' => 'fixed-width picture'),
							array('label' => '文件', 'url' => array('/file/index'), 'icon' => 'fixed-width file'),
							array('label' => '用户', 'url' => array('/user/index'), 'icon' => 'fixed-width user'),
							array('label' => '角色', 'url' => array('/role/index'), 'icon' => 'fixed-width group'),
							array('label' => '权限', 'url' => array('/permission/index'), 'icon' => 'fixed-width lock'),
							array('label' => '设置', 'url' => array('/site/setting'), 'icon' => 'fixed-width cog'),
						)
					)
				);
				?>
				</div> <!-- /.sidebar -->
				<div class="main-content">
					<a class="btn btn-default btn-layout-full" href="javascript:void(0)" id="btn-layout-full" title="全屏"><i class="icon-fullscreen"></i></a>
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
