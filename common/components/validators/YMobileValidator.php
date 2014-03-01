<?php
/**
 * YMobileValidator class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YMobileValidator
 * 用于手机号码的验证
 *
 * @author Yang <css3@qq.com>
 * @package common.components.validators
 */
class YMobileValidator extends CValidator
{
	/**
	 * @var boolean
	 */
	public $skipOnError = true;

	/**
	 * @var string
	 */
	public $pattern = '/^1[3|4|5|8][0-9]\d{4,8}$/';

	/**
	 * @see CValidator::validateAttribute()
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if (!preg_match($this->pattern, $value)) {
			$this->addError($object, $attribute, '{attribute} 格式不正确');
		}
	}
}