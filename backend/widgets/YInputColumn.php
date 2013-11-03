<?php
/**
 * YInputColumn Class File
 *
 * @author yang <css3@qq.com>
 */

Yii::import('zii.widgets.grid.CGridColumn');

/**
 * YInputColumn
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YInputColumn extends CGridColumn
{
	/**
	 * 属性名
	 * @var string
	 */
	public $name;

	/**
	 * @var mxied
	 */
	public $value;

	/**
	 * @var array
	 */
	public $htmlOptions = array('class'=>'input-column');

	/**
	 * @var array
	 */
	public $headerHtmlOptions = array('class'=>'input-column');

	/**
	 * @var array
	 */
	public $footerHtmlOptions = array('class'=>'input-coluumn');

	/**
	 * @var array
	 */
	public $inputHtmlOptions = array();

	/**
	 * @var boolean 属性是否可以排序
	 */
	public $sortable = true;

	/**
	 * @var string 模型主键
	 */
	public $primaryKey = 'id';

	public function init()
	{
		if (isset($this->inputHtmlOptions['name'])) {
			$name = $this->inputHtmlOptions['name'];
		} else {
			$name = $this->id;
			if (substr($name, -2) !== '[]') {
				$name .= '[]';
			}
			$this->inputHtmlOptions['name'] = $name;
		}
	}

	public function renderHeaderCellContent()
	{
		echo $this->getHeaderCellContent();
	}

	public function renderDataCellContent($row)
	{
		echo $this->getDataCellContent($row);
	}

	public function getHeaderCellContent()
	{
		if ($this->grid->enableSorting && $this->sortable && $this->name !== null) {
			$sort = $this->grid->dataProvider->getSort();
			$label = isset($this->header) ? $this->header : $sort->resolveLabel($this->name);

			if ($sort->resolveAttribute($this->name) !== false)
				$label .= '<span class="caret"></span>';

			return $sort->link($this->name, $label, array('class'=>'sort-link'));
		} else {
			if ($this->name !== null && $this->header === null) {
				if ($this->grid->dataProvider instanceof CActiveDataProvider)
					return CHtml::encode($this->grid->dataProvider->model->getAttributeLabel($this->name));
				else
					return CHtml::encode($this->name);
			} else
				return CHtml::encode($this->header);
		}
	}

	public function getDataCellContent($row)
	{
		$data = $this->grid->dataProvider->data[$row];
		if ($this->value !== null) {
			$value = $this->evaluateExpression($this->value, array('data'=>$data, 'row'=>$row));
		} elseif ($this->name !== null) {
			$value = CHtml::value($data, $this->name);
		} else {
			$value = $this->grid->dataProvider->keys[$row];
		}

		$options = $this->inputHtmlOptions;
		$name = $options['name'];
		unset($options['name']);
		$options['value'] = $value;
		$options['id'] = $this->id . '_' . $row;
		return CHtml::textField($name . '[' . $data[$this->primaryKey] . ']', $value, $options);
	}
}