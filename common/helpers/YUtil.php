<?php
class YUtil
{
	/**
	 * 截取字符串长度
	 * @param string $str
	 * @param integer $length
	 * @param string $more
	 * @return string
	 */
	public static function substr($str, $length, $more='...')
	{
		$oLength = mb_strlen($str, Yii::app()->charset);
		if ($oLength <= $length)
			return $str;
		else
			return mb_substr($str, 0, $length, Yii::app()->charset) . $more;
	}

	/**
	 * 时间过滤列表
	 * @return array
	 */
	public static function timeFilterList()
	{
		return array(
			'-3 day' => '最近三天',
			'-1 week' => '最近一个星期',
			'-1 month' => '最近一个月',
			'-3 month' => '最近三个月',
			'-1 year' => '最近一年',
		);
	}

	/**
	 * 生成时间条件
	 * @param string $value 时间字符串
	 * @param string $attribute 模型属性
	 * @return string
	 */
	public static function generateTimeCondition($value, $attribute)
	{
		$time = strtotime($value);
		if ($time == false)
			return;
		$now = time();
		return "{$attribute} BETWEEN $time AND $now";
	}

	/**
	 * 中文星期
	 * @param integer $w
	 * @return string
	 */
	public static function week($w)
	{
		switch ($w) {
			case 1:
				$str = '一';
				break;
			case 2:
				$str = '二';
				break;
			case 3:
				$str = '三';
				break;
			case 4:
				$str = '四';
				break;
			case 5:
				$str = '五';
				break;
			case 6:
				$str = '六';
				break;
			case 0:
			default:
				$str = '日';
				break;
		}

		return '星期' . $str;
	}

	public static function mkdir($dst,array $options,$recursive)
	{
		$prevDir=dirname($dst);
		if($recursive && !is_dir($dst) && !is_dir($prevDir))
			self::mkdir(dirname($dst),$options,true);

		$mode=isset($options['newDirMode']) ? $options['newDirMode'] : 0777;
		$res=mkdir($dst, $mode);
		@chmod($dst,$mode);
		return $res;
	}
}