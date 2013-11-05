<?php
/**
 * YTaxonomyListViewWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YTaxonomyListViewWidget
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YTaxonomyListViewWidget extends CWidget
{
	/**
	 * 模型
	 * @var Node
	 */
	public $model;

	/**
	 * 运行
	 * @see CWidget::run()
	 */
	public function run()
	{
		if (!isset($this->model))
			return;

		$terms = $this->model->getTaxonomy();
		$taxonomies = $this->model->prepareTaxonomies();
		foreach ($this->model->prepareTaxonomies() as $taxonomy => $params) {
			echo '<dl><dt>' . CHtml::encode($params['label']) . '</dt>';
			if (isset($terms[$taxonomy])) {
				$termNames = array();
				if (is_array($terms[$taxonomy])) {
					foreach ($terms[$taxonomy] as $term) {
						$termNames[] = $term->name;
					}
				} else
					$termNames[] = $terms[$taxonomy]->name;

				echo '<dd>' . implode('</dd> <dd>', array_map(array(CHtml, 'encode'), $termNames)) . '</dd>';
			}
			echo '</dl>';
		}
	}
}