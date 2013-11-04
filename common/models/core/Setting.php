<?php
/**
 * Setting Class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * Setting 表单模型
 *
 * @author Yang <css3@qq.com>
 * @package backend.models.core
 */
class Setting extends CFormModel
{
	/**
	 * 当前设置分类
	 * @var string
	 */
	protected $category;

	/**
	 * 当前设置操作选项
	 * @var array
	 */
	protected $options = array();

	/**
	 * 设置分类
	 * @var string
	 */
	private static $_categories = array(
		'general' => '一般设置',
		'system' => '系统设置',
	);

	/**
	 * 当前设置所有选项标签
	 * @var array|null
	 */
	private $_labels;

	/**
	 * 绑定分类 设置当前分类
	 * @param string $category
	 */
	public function bindCategory($category)
	{
		if (!isset(self::$_categories[$category]))
			return;

		$settings = isset(Yii::app()->params['settings']) ? Yii::app()->params['settings'] : array();
		if (!isset($settings[$category]))
			return;

		$this->category = $category;
		$this->options = (array) $settings[$category];
		//从设置选项中填充值
		foreach (self::getAllByCategory($category) as $key => $value) {
			if (isset($this->options[$key]))
				$this->options[$key]['value'] = $value;
		}
	}

	/**
	 * 注册一个分类
	 * @param string $category
	 * @param string $description 分类描述
	 */
	public static function registerCategory($category, $description)
	{
		if (!isset(self::$_categories[$category]))
			self::$_categories[$category] = $description;
	}

	/**
	 * 获取已注册的分类列表
	 * @return array
	 */
	public static function getCategories()
	{
		return self::$_categories;
	}

	/**
	 * 获取当前设置选项的字段规则
	 * @see CModel::rules()
	 */
	public function rules()
	{
		$rules = array();
		$validators = array();
		foreach ($this->options as $name => $params) {
			if (isset($params['required']))
				$validators['required'][] = $name;

			$type = isset($params['type']) ? $params['type'] : 'text';

			switch ($type) {
				case 'url':
					$validators['url'][] = $name;
					break;
				case 'date':
					$validators['date'][] = $name;
					break;
				case 'email':
					$validators['email'][] = $name;
					break;
				case 'file':
					$validators['file'][] = $name;
					break;
				case 'number':
					$validators['numerical'][] = $name;
					break;
				case 'html':
					$validators['filter'][] = $name;
					break;
				case 'bool':
					$validators['boolean'][] = $name;
					break;
				case 'textarea':
					$validators['safe'][] = $name;
					break;
				default:
					$validators['length'][] = $name;
					break;
			}
		}

		foreach ($validators as $validator => $names) {
			if ($validator === 'html')
				$params = array('filter'=>array($obj=new CHtmlPurifier(),'purify'));
			else if ($validator === 'length')
				$params = array('max' => 255);
			else
				$params = array();
			$rules[] = array_merge(array(implode(',', $names), $validator), $params);
		}
		return $rules;
	}

	/**
	 * 获取当前设置选项字段标签
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		if (!isset($this->_labels)) {
			$this->_labels = array();
			foreach ($this->options as $name => $params) {
				$label = isset($params['label']) ? $params['label'] : $name;
				$this->_labels[$name] = $label;
			}
		}
		return $this->_labels;
	}

	/**
	 * 保存设置
	 * @return boolean
	 */
	public function save()
	{
		if (!$this->validate())
			return false;

		$category = $this->category;
		if (empty($category))
			return false;

		foreach ($this->options as $name => $params) {
			self::set($category, $name, isset($params['value']) ? $params['value'] : '', false);
		}
		Yii::app()->getCache()->delete("setting_{$category}_options");
		return true;
	}

	/**
	 * 获取分类下的所有设置选项
	 * @param string $category
	 * @return array
	 */
	public static function getAllByCategory($category) {
		$cacheKey = "setting_{$category}_options";
		if (($options = Yii::app()->getCache()->get($cacheKey)) === false) {
			$rows = Yii::app()->getDb()->createCommand()
				->select(array('key', 'value'))
				->from('{{setting}}')
				->where('category=:category', array(':category' => $category))
				->queryAll();
			$options = array();
			foreach ($rows as $row) {
				$options[$row['key']] = $row['value'];
			}
			Yii::app()->getCache()->set($cacheKey, $options);
		}
		return $options;
	}

	/**
	 * 设置一个选项
	 * @param string $category
	 * @param string $key
	 * @param string $value
	 * @param boolean $updateCache 是否更新缓存
	 */
	public static function set($category, $key, $value='', $updateCache=true)
	{
		$oldValue = self::get($category, $key);
		if ($oldValue !== null) {
			if ($oldValue == $value)
				return;
			else {
				Yii::app()->getDb()->createCommand()->update('{{setting}}', array(
					'value' => $value,
				), '`category`=:category AND `key`=:key', array(
					':category' => $category,
					':key' => $key,
				));
			}
		} else {
			Yii::app()->getDb()->createCommand()->insert('{{setting}}', array(
				'category' => $category,
				'key' => $key,
				'value' => $value,
			));
		}

		if ($updateCache) {
			$options = self::getAllByCategory($category);
			$options[$key] = $value;
			Yii::app()->getCache()->set("setting_{$category}_options", $options);
		}
	}

	/**
	 * 获取一个选项值
	 * @param string $category
	 * @param string $key
	 * @param mixed $default 缺省值
	 * @return mixed
	 */
	public static function get($category, $key, $default=null)
	{
		$options = self::getAllByCategory($category);
		return isset($options[$key]) ? $options[$key] : $default;
	}

	/**
	 * 获取当前设置的选项
	 * @return array
	 */
	public function getOptions()
	{
		$options = $this->options;
		foreach ($options as $name => $params) {
			if ($name[0] === '_') //私有的
				unset($options[$name]);
		}
		return $options;
	}

	public function __set($name, $value)
	{
		if ($name[0] === '_') //私有的
			return;

		if (isset($this->options[$name]))
			$this->options[$name]['value'] = $value;
		else
			parent::__set($name, $value);
	}

	public function __get($name)
	{
		if (isset($this->options[$name])) {
			if (isset($this->options[$name]['value']))
				return $this->options[$name]['value'];
			elseif (isset($this->options[$name]['default']))
				return $this->options[$name]['default'];
			else
				return;
		} else
			parent::__get($name);
	}

	public function __unset($name)
	{
		if ($name[0] === '_') //私有的
			return;

		if (isset($this->options[$name]))
			unset($this->options[$name]['value']);
		else
			parent::__unset($name);
	}
}