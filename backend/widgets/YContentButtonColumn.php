<?php
/**
 * YContentButtonColumn Class File
 *
 * @author yang <css3@qq.com>
 */

Yii::import('bootstrap.widgets.TbButtonColumn');

/**
 * YContentButtonColumn
 *
 * @author yang <css3@qq.com>
 * @package backend.widgets
 */
class YContentButtonColumn extends TbButtonColumn
{
	/**
	 * @var Channel 栏目
	 */
	public $channel;

	public function initDefaultButtons()
	{
		if (!$this->channel instanceof Channel)
			throw new CException('栏目没有设置');

		$this->viewButtonUrl='Yii::app()->controller->createUrl("view",array("id"=>$data->primaryKey, "channel"=>' . $this->channel->id . '))';
		$this->updateButtonUrl='Yii::app()->controller->createUrl("update",array("id"=>$data->primaryKey,"channel"=>' . $this->channel->id . '))';
		$this->deleteButtonUrl='Yii::app()->controller->createUrl("delete",array("id"=>$data->primaryKey, "channel"=>' . $this->channel->id . '))';
		parent::initDefaultButtons();
	}
}