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
	<div class="span6 column">
		<?php
		$this->widget('bootstrap.widgets.TbBox', array(
			'title' => '欢迎',
			'content' => ''
			)
		);
		?>

	</div>
	<div class="span6 column">
		<?php
		$this->widget('bootstrap.widgets.TbBox', array(
			'title' => 'Basic Box',
			'content' => 'My Basic Content (you can use renderPartial here too :))')
		);
		?>
	</div>
</div>
