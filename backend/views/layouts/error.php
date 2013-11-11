
<?php /* @var $this SiteController */ ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body class="error-page">
<?php $this->widget('bootstrap.widgets.TbNavbar',array(
	'fixed'=>false,
	'type'=>'inverse',
	'collapse'=>true,
	'items'=>array(
		array(
			'class'=>'bootstrap.widgets.TbMenu',
			'htmlOptions'=>array(
				'class'=>'pull-right',
			),
			'items'=>array(
				array('label'=>'回到首页', 'url'=>Yii::app()->getBaseUrl(true), 'icon'=>'chevron-left white'),
			),
		),
	),
)); ?>
	<div class="error-content">
		<?php $this->widget('bootstrap.widgets.TbAlert'); ?>
		<?php echo $content; ?>
	</div>
</body>
</html>
