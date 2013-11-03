<?php
/**
 * YBulkActions Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YBulkActions
 *
 * @see TbBulkActions
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YBulkActions extends CComponent
{
	/**
	 * @var array
	 */
	public $actionButtons = array();

	/**
	 * 对齐
	 * @var string
	 */
	public $align = 'left';

	public function init()
	{
		$this->align = $this->align === 'right' ? 'pull-right' : 'pull-left';
	}

	public function renderButtons()
	{
		echo CHtml::openTag('div', array('class' => $this->align . ' bulk-actions'));
		echo CHtml::dropDownList('action', null, $this->actionButtons, array('id' => 'bulk-select'));
		echo CHtml::htmlButton('应用', array('class' => 'btn btn-primary', 'name' => 'doaction', 'type' => 'submit', 'disabled'=>'disabled'));
		echo CHtml::closeTag('div');
		$this->registerScript();
	}

	/**
	 * 注册脚本
	 */
	public function registerScript()
	{
		if (isset($this->actionButtons['delete'])) {
			$cs = Yii::app()->getClientScript();
			$js = <<<EOT

$(document).on('click', '.checkbox-column input[type=checkbox]', function(e) {
	var disabled;
	if ($('.checkbox-column input:checked').length) {
		disabled = false;
	} else {
		disabled = true;
	}
	$('button[name=doaction]').attr('disabled', disabled);
});
$('button[name=doaction]').click(function() {
	if ($('#bulk-select').val() == 'delete') {
		return window.confirm('确定要删除这些数据吗?');
	}
	return true;
});
EOT;
			$cs->registerScript(__CLASS__, $js);
		}
	}
}