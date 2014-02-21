<?php
class timepicker2 extends CInputWidget
{
	/**
	 * @var TbActionForm
	 */
	public $form;

	/**
	 * @var string
	 */
	public $language;

	/**
	 * @var string
	 */
	public $select = 'datetime';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	protected $assets;

	public function registerScript() {
		$this->assets = Yii::app()->assetManager->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');

		Yii::app()->clientScript
			->registerCoreScript( 'jquery' )
			->registerCoreScript( 'jquery.ui' )
			->registerScriptFile( $this->assets.'/js/jquery.ui.timepicker.js' )
			//->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css')
			->registerCssFile( $this->assets.'/css/timepicker.css' );

		if (!isset($this->language)) {
			$this->language = substr(Yii::app()->getLanguage(), 0, 2);
		}

		if ($this->language) {
			$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
			$langFile = '/js/jquery.ui.timepicker.'.$this->language.'.js';

			if (is_file($path.DIRECTORY_SEPARATOR.$langFile))
				Yii::app()->clientScript->registerScriptFile($this->assets.$langFile);
		}

		$default = array(
			'dateFormat'=>'yy-mm-dd',
			'timeFormat'=>'hh:mm:ss',
			//'showOn'=>'button',
			'showSecond'=>false,
			'changeMonth'=>false,
			'changeYear'=>false,
		);

		$this->options = array_merge($default, $this->options);
	}

	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if ($this->hasModel()) {
			if (CHtml::value($this->model, $this->attribute) == 0) {
				$this->htmlOptions['value'] = '';
			}
			if ($this->form) {
				$input = $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
				if ($this->form instanceof TbActiveForm) {
					echo $this->form->customRow($this->model, $this->attribute, $input, $this->htmlOptions);
				} else {
					echo $input;
				}
			} else {
				echo CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			}
		} else {
			echo CHtml::textField($name, $this->value, $this->htmlOptions);
		}

		if (empty($this->htmlOptions['disabled'])) {
			$this->registerScript();
			$options = !empty($this->options) ? CJavaScript::encode($this->options) : '';
			Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id,"
				jQuery('#{$id}').".$this->select."picker({$options});
			");
		}
	}
}