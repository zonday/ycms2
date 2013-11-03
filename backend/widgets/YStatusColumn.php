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
	 * @var array
	 */
	public $statusList = array();

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

		$value = $value === null ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);

		if (!isset($this->labelMap[$value])) {
			$label = 'default';
		} else {
			$label = $this->labelMap[$value];
		}

		if (isset($this->statusList[$value])) {
			$value = $this->statusList[$value];
		}
		echo '<span class="label label-' . $label . '">' . $value . '</span>';
	}
}