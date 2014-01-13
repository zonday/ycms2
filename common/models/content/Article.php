<?php
/**
 * Article class file
 *
 * @author yang <css3@qq.com>
 */

/**
 * Article Model
 *
 * @property string $content
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Article extends Node
{
	public $image;

	/**
	 * 获取模型
	 * @param string $className
	 * @return Article $model
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function className()
	{
		return 'Article';
	}

	public function tableName()
	{
		return '{{article}}';
	}

	public function extraRules()
	{
		return array(
			array('content', 'filter', 'filter'=>array($obj=new CHtmlPurifier(),'purify')),
			array('termIds, image', 'safe'),
		);
	}

	public function taxonomies()
	{
		return array();
	}

	public function fileUsage()
	{
		return array();
	}

	public function extraBehaviors(){
		$behaviors = array();
		if ($this->fileUsage()) {
			$behaviors['YFileUsageBehavior'] = array(
				'class' => 'YFileUsageBehavior',
				'fields' => $this->fileUsage(),
			);
		}
		if ($this->taxonomies()) {
			$behaviors['YTaxonomyBehavior'] = array(
				'class'=>'YTaxonomyBehavior',
				'taxonomies'=>$this->taxonomies(),
			);
		}
		return $behaviors;
	}

	public function extraLabels()
	{
		return array(
			'id' => 'ID',
			'content' => '内容',
			'image'=>'特色图',
			'taxonomy'=>'分类',
		);
	}

	public function getTheExcerpt($length=100, $more='...')
	{
		if (empty($this->excerpt)) {
			return YUtil::substr(strip_tags($this->content), $length, $more);
		} else {
			return YUtil::substr($this->excerpt, $length, $more);
		}
	}
}