<?php
/**
 * YStatusColumn Class File
 *
* @author yang <css3@qq.com>
*/

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * YStatusColumn
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YStatusColumn extends CDataColumn
{
	/**
	 * label列表
	 * @var array
	 */
	public $labelMap = array();

	/**
	 * 状态列表
	 * @var mixed
	 */
	public $statusList;

	/**
	 * @var array
	 */
	public $htmlOptions = array('class' => 'status-column');

	/**
	 * @var array
	 */
	public $headerHtmlOptions = array('class' => 'status-column');

	/**
	 * @var array
	 */
	public $footerHtmlOptions = array('class' => 'status-column');

	/**
	 * @see CGridColumn::renderDataCellContent()
	 */
	protected function renderDataCellContent($row, $data)
	{
		$data = $this->grid->dataProvider->data[$row];

		if($this->value !== null) {
			$value = $this->evaluateExpression($this->value, array('data'=>$data, 'row'=>$row));
		} elseif($this->name !== null) {
			$value = CHtml::value($data,$this->name);
		}

		if ($value !== null)
			$value = $this->grid->getFormatter()->format($value, $this->type);

		if (!isset($this->labelMap[$value])) {
			$label = 'default';
		} else {
			$label = $this->labelMap[$value];
		}

		if (is_string($this->statusList) && method_exists($data, $this->statusList)) {
			$statusList = call_user_func(array($data, $this->statusList));
		} else if (is_array($this->statusList)) {
			$statusList = $this->statusList;
		} else {
			$statusList = array();
		}

		if (isset($statusList[$value])) {
			$value = $statusList[$value];
		} else {
			$value = null;
		}
		if ($value) {
			echo '<span class="label label-' . $label . '">' . $value . '</span>';
		} else {
			echo '-';
		}
	}
}