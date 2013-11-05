<?php
/**
 * YTopSearchWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YTopSearchWidget
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YTopSearchWidget extends CWidget
{
	/**
	 * @var array
	 */
	public $actions = array();

	/**
	 * 运行
	 * @see CWidget::run()
	 */
	public function run()
	{
		$this->registerScript();
		?>
		<form method="get" class="top-search-form navbar-form pull-left" id="top-search">
		<div class="input-append">
			<input class="span2" id="appendedDropdownButton" type="text" name="s">
			<input type="hidden" name="r" id="route">
			<div class="btn-group">
				<button class="btn dropdown-toggle" data-toggle="dropdown">
					<i class="icon-search"></i> 搜索 <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<?php foreach ($this->actions as $value => $name): ?>
					<li><a href="#" data-action="<?php echo $value; ?>"><i class="icon-search"></i> <?php echo CHtml::encode($name); ?></a>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		</form>
		<?php
	}

	/**
	 * 注册脚本
	 */
	public function registerScript()
	{
		$cs = Yii::app()->getClientScript();
		$js = <<<EOT
$('#top-search .dropdown-menu a').click(function() {
	$('#route').val($(this).data('action'));
	$('#top-search').submit();
})
EOT;
		$cs->registerScript(__CLASS__.'#'.$this->id, $js);
	}
}
