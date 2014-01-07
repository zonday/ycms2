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
	 * @return CActiveRecord $model
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
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
		return array(
			'tags'=>array(
				'label'=>'标签',
				'many'=>true,
				'allowEmpty'=>false,
				'custom'=>true,
			)
		);
	}

	public function fileUsage()
	{
		return array(
			'image' => array(
				'location' => 'public://article/' . date('Ym'),
				'type' => 'image',
				'resize' => array(
					'post-thumbnail'=>array(222, 157, true),
				)
			),
		);
	}

	public function extraBehaviors(){
		return array(
			'YFileUsageBehavior' => array(
				'class' => 'YFileUsageBehavior',
				'fields' => $this->fileUsage(),
			),
			'YTaxonomyBehavior' => array(
				'class'=>'YTaxonomyBehavior',
				'taxonomies'=>$this->taxonomies(),
			),
		);
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
}