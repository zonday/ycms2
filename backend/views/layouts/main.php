<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex,nofollow" />
		<title><?php echo CHtml::encode($this->pageTitle . ' - ' . Yii::app()->name); ?></title>
	</head>
	<body class="sticky-menu">
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
		<div class="main-container clearfix" id="main-container">
			<div id="sidebarback" class="sidebarback"></div>
			<div class="sidebar" id="sidebar">
			<?php
			$this->widget(
				'bootstrap.widgets.TbMenu',
				array(
					'type' => 'list',
					'items' => $this->generateNavItems(),
				)
			);
			?>
			</div> <!-- /.sidebar -->
			<a class="btn btn-default btn-layout-full" href="javascript:void(0)" id="btn-layout-full" title="全屏"><i class="icon-fullscreen"></i></a>
			<div class="breadcrumbs">
				<?php if (isset($this->breadcrumbs)): ?>
					<?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
						'links' => $this->breadcrumbs,
					)); ?><!-- breadcrumbs -->
				<?php endif?>
			</div>
			<div class="main-content clearfix">
				<div class="page-content">
					<div class="container-fluid">
					<?php $this->widget('bootstrap.widgets.TbAlert'); ?>
					<?php echo $content; ?>
					</div>
				</div> <!-- /.page-content -->
			</div> <!-- /.main-content -->
		</div> <!-- /.main-container -->
	</body>
</html>
