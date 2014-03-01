<?php
/**
 * YIdCardValidator class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YIdCardValidator
 * 用于身份证号码的验证
 *
 * @author Yang <css3@qq.com>
 * @package common.components.validators
 */
class YIdCardValidator extends CValidator
{
	/**
	 * @var string
	 */
	public $message = '{attribute} 格式不正确';

	/**
	 * @var integer
	 */
	public $allowLength = 18;

	/**
	 * @var boolean
	 */
	public $allowEmpty = true;

	/**
	 * @see CValidator::validateAttribute()
	 */
	public function validateAttribute($object, $attribute) {
		$value = $object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		if ($this->allowLength !== false) {
			$this->allowLength = array(15, 18);
		} else {
			$this->allowLength = (array) $this->allowLength;
		}

		$len = strlen($value);

		if (!in_array($len, $this->allowLength)) {
			$this->addError($object, $attribute, $this->message);
			return;
		}

		if ($len == 18 && preg_match('/^(\d{17})(\d|x|X)$/', $value, $matches)) {
			if (strtolower(self::getVCode($matches[1])) !== strtolower($matches[2])) {
				$this->addError($object, $attribute, $this->message);
				return;
			}
		} elseif ($len == 15 && preg_match('/^\d{15}$/', $value)) {
			$value = self::to18IdCard($value);
		} else {
			$this->addError($object, $attribute, $this->message);
			return;
		}

		$validProvince = array(
			11, 12, 13, 14, 15, 21, 22, 23, 31, 32, 33, 34, 35, 36, 37, 41, 42, 43, 44, 45, 46, 50, 51, 52,
			53, 54, 61, 62, 63, 64, 65, 71, 81, 82,
		);

		list($year, $month, $day) = self::getBirthDayByIdCard($value);
		if (!checkdate($month, $day, $year) || !in_array(substr($value, 0, 2), $validProvince)) {
			$this->addError($object, $attribute, $this->message);
			return;
		}
	}

	/**
	 * 获取生日年月日
	 * @param string $value
	 * @return array 年,月,日
	 */
	public static function getBirthDayByIdCard($value)
	{
		return array(substr($value, 6, 4), substr($value, 10, 2), substr($value, 12, 2));
	}

	/**
	 * 获取性别
	 * @param string $value
	 * @return number 1男 0女
	 */
	public static function getGenderByIdCard($value)
	{
		return $value[16] % 2;
	}

	/**
	 * 15位转换成18位
	 * @param unknown_type $value
	 * @return number
	 */
	public static function to18IdCard($value)
	{
		$value = substr($value, 0, 6) + '19' + substr($value, 6, 9);
		return $value + self::getVCode($value);
	}

	/**
	 * 获取身份证号校验码
	 * @param string $value 17位数
	 * @return string
	 */
	public static function getVCode($value)
	{
		$value = (string) $value;
		$c = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
		$v = array(1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2);
		$sum = 0;
		for ($i = 0, $l = strlen($value); $i < $l; $i++ ) {
			$sum += ($c[$i] * $value[$i]);
		}
		return $v[$sum % 11];
	}
}