<?php
/**
 * YAttachmentViewWidget Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * YAttachmentViewWidget
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YAttachmentViewWidget extends CWidget
{
	/**
	 * @var CActiveRecord 模型
	 */
	public $model;

	public function run()
	{
		if (!isset($this->model)) {
			return;
		}

		$this->render('atachment-view', array('models'=>$this->model->getAttachmentFiles()));
	}
}