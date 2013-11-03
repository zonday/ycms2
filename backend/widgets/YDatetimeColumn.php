<?php
/**
 * YDatetimeColumn Class File
 *
* @author yang <css3@qq.com>
*/

Yii::import('zii.widgets.grid.CDataColumn');

/**
 * YDatetimeColumn
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YDatetimeColumn extends CDataColumn
{
	public $nullDisplay;

	/**
	 * @var array
	 */
	public $htmlOptions = array('class' => 'datetime-column');

	/**
	 * @var array
	 */
	public $headerHtmlOptions = array('class' => 'datetime-column');

	/**
	 * @var array
	 */
	public $footerHtmlOptions = array('class' => 'datetime-column');

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

		$formatValue = !$value ? $this->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
		if ($value) {
			echo '<abbr title="' . date('Y-m-d H:i:s', $value) . '">' . $formatValue .' </abbr>';
		} else {
			echo $formatValue;
		}
	}
}