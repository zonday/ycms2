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
				$params['multiple']=false;
				CHtml::resolveNameID($model, $attribute, $htmlOptions);
				$this->registerCascadeScript($htmlOptions['id'], $params['allowEmpty']);
			} elseif ($params['channel']) {
				$termSlug = $this->getOwner()->getChannel()->name;
				$term = Term::model()->findFromCacheBySlug($termSlug, $params['taxonomy']->id);
				if ($term)
					$list = CHtml::listData(Term::model()->getTree($params['taxonomy']->id, $term->id), 'id', 'name');
				else
					$list = array();
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
		$url = Yii::app()->createUrl('ajax/cascade');
		$allowEmpty = $allowEmpty ? 'true' : 'false';
		$js=<<<EOT
(function() {
	var root = $('#$id');
	var allowEmpty = $allowEmpty;

	function cascade(prevObject, level) {
		var id = prevObject.val();

		var insertSelectId = root.attr('id') + '-' + level;
		var insertSelect = jQuery('#' + insertSelectId);

		if (!insertSelect.get(0)) {
			insertSelect = jQuery('<select></select>').attr({
				'name': root.attr('name'),
				'class': root.attr('class'),
				'id': insertSelectId
			});

			insertSelect.delete = function() {
				this.remove();
				this.nextSelect && this.nextSelect.delete();
			}
			insertSelect.change(change(insertSelect, ++level));
			prevObject.after(insertSelect);
			prevObject.nextSelect = insertSelect;
		}

		if (!id) {
			prevObject.nextSelect && prevObject.nextSelect.delete();
			return;
		}

		$.ajax({
			type: 'get',
			url: '$url' + '&id=' + id,
			success: function(response) {
				try {
					data = jQuery.parseJSON(response);
				} catch (e) {
					return;
				}

				if (data) {
					var html = '';
					if (allowEmpty)
						html = '<option value=""></option>';

					jQuery.each(data, function(id, name){
						html += '<option value="' + id + '">' + name + '</option>';
					});

					insertSelect.html(html);
					insertSelect.change();
				} else {
					prevObject.nextSelect && prevObject.nextSelect.delete();
				}
			}
		});
	}

	function change(object, level) {
		return function() {
			cascade(object, level);
		}
	}

	root.change(change(root, 1)).change();
})();

EOT;
		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$id, $js);
	}
}