<?php
/**
 * YTaxonomyFilterWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YTaxonomyFilterWidget
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YTaxonomyFilterWidget extends CWidget
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
	public function run() {
		if (!isset($this->model))
			return;

		$data = array();
		foreach ($this->model->prepareTaxonomies() as $params) {
			$htmlOptions = array();
			if ($params['cascade'] && $params['taxonomy']->hierarchy == Taxonomy::HIERARCHY_MULTIPLE) {
				$attribute = "taxonomy[{$taxonomy}]";
				$list = CHtml::listData(Term::model()->getTree($params['taxonomy']->id, 0, 1), 'id', 'name');
				$params['many']=false;
				CHtml::resolveNameID($model, $attribute, $htmlOptions);
				$this->registerCascadeScript($htmlOptions['id'], $params['allowEmpty']);
			} else {
				$list = Term::model()->generateTreeList($params['taxonomy']->id);
			}
			$data[] = array('taxonomy'=>$params['taxonomy'], 'list'=>$list, 'params'=>$params);
		}

		$this->render('taxonomy-filter', array('data'=>$data, 'model'=>$this->model));
	}

	/**
	 * 注册级联脚本
	 * @todo 测试
	 * @param mixed $id
	 * @param boolean $allowEmpty
	 */
	public function registerCascadeScript($id, $allowEmpty)
	{
		$allowEmpty = $allowEmpty ? 'true' : 'false';
		$js=<<<EOT

EOT;
		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$id, $js);
	}
}