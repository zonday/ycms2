<?php
/**
 * YGridView Class File
 *
 * @author yang <css3@qq.com>
 */

Yii::import('bootstrap.widgets.TbGridView');

/**
 * YGridView
 *
 * @see TbGridView
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YGridView extends TbGridView
{
	public $actions;

	public $template = "{actions}\n{summary}\n{items}\n{pager}";

	public $actionsHtmlOptions = array();

	public function renderActions() {
		if (!isset($this->actionsHtmlOptions['class'])) {
			$this->actionsHtmlOptions['class'] = 'span2';
		}

		$this->actionsHtmlOptions['id'] = 'bulk-select';

		if (is_array($this->actions)) {
			echo CHtml::openTag('div', array('class' => 'actions'));
			echo CHtml::dropDownList('action', null, $this->actions, $this->actionsHtmlOptions);
			echo CHtml::htmlButton('应用', array('class' => 'btn', 'name' => 'doaction', 'type' => 'submit'));
			echo CHtml::closeTag('div');
		}

		$this->registerScript();
	}

	/**
	 * 注册脚本
	 */
	public function registerScript()
	{
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
EOT;
		if (isset($this->actions['delete'])) {
			$js .= <<<EOT
$(document).on('click', 'button[name=doaction]', function(e) {
	if ($('#bulk-select').val() == 'delete') {
		return window.confirm('确定要删除这些数据吗?');
	}
	return true;
});
EOT;
		}
		$cs->registerScript(__CLASS__, $js);
	}
}