<?php
class YMobileValidator extends CValidator
{
	public $skipOnError = true;

	public $pattern = '/^1[3|4|5|8][0-9]\d{4,8}$/';

	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if (!preg_match($this->pattern, $value)) {
			$this->addError($object, $attribute, '{attribute} 格式不正确');
		}
	}
}