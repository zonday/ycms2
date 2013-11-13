<?php
/**
 * YTaxonomyWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YTaxonomyWidget
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YTaxonomyWidget extends CInputWidget
{
	/**
	 * 表单模型
	 * @var unknown_type
	 */
	public $form;

	/**
	 * 运行
	 * @see CWidget::run()
	 */
	public function run()
	{
		$model = $this->model;
		$form = $this->form;

		foreach ($model->prepareTaxonomies() as $taxonomy => $params) {
			$htmlOptions = $this->htmlOptions;

			if ($params['many'])
				$htmlOptions['multiple'] = true;

			if ($params['allowEmpty'])
				$htmlOptions['empty']= '无';

			if ($params['input'] == 'checkbox')
				$input = 'checkboxList';
			else
				$input = 'dropDownList';

			if ($params['custom'])
				$input = 'textField';

			$attribute = "termIds[{$taxonomy}]";

			if ($params['cascade'] && $params['taxonomy']->hierarchy == Taxonomy::HIERARCHY_MULTIPLE) {
				$list = CHtml::listData(Term::model()->getTree($params['taxonomy']->id, 0, 1), 'id', 'name');
				$htmlOptions['many']=false;
				CHtml::resolveNameID($model, $attribute, $htmlOptions);
				$this->registerCascadeScript($htmlOptions['id'], $params['allowEmpty']);
			} else {
				$list = Term::model()->generateTreeList($params['taxonomy']->id);
			}

			$errorClass = 'help-block' . ($model->hasErrors($attribute) ? ' error' : '');
			if ($this->form->type == 'horizontal') {
				echo '<div class="control-group">';
				echo $form->labelEx($model, $attribute, array('label'=>$params['label'], 'class'=>'control-label'));
				echo '<div class="controls">';
				if ($params['custom']) {
					echo $form->textField($model, $attribute, $htmlOptions);
				} else {
					echo $form->$input($model, $attribute, $list, $htmlOptions);
				}

				echo $form->error($model, $attribute, array('class'=>$errorClass));
				echo '</div></div>';
			} else {
				echo $form->labelEx($model, $attribute, array('label'=>$params['label']));
				if ($params['custom']) {
					echo $form->textField($model, $attribute, $htmlOptions);
				} else {
					echo $form->$input($model, $attribute, $list, $htmlOptions);
				}
				echo $form->error($model, $attribute, array('class'=>$errorClass));
			}
		}
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