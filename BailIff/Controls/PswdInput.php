<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\controls;

use Nette\Framework,
	Nette\Forms\TextInput,
	Nette\Forms\Form,
	Nette\Web\Html,
	Nette\Templates\TemplateHelpers,
	Nette\Json;

/**
 * Improved password input control
 * - CAPS-LOCK warning
 * - show password checkbox
 * - masked password
 * @author Lopo <lopo@losys.eu>
 */
class PswdInput
extends TextInput
{
	/** @var Html */
	protected $container;
	/**
	 * using CAPS-lock warning
	 * @var bool
	 */
	public $useClWarning=TRUE;
	/**
	 * using Show Password Checkbox
	 * @var bool
	 */
	public $useShowPswd=FALSE;
	/**
	 * using masking
	 * @var bool
	 */
	public $useMasked=FALSE;
	/** @var string */
	public $strClWarning='Caps-lock is ON!';
	/** @var string */
	public $icoClWarning='data:image/png;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAARABEDASIAAhEBAxEB/8QAGQABAAMBAQAAAAAAAAAAAAAAAAIEBgUH/8QALxAAAAQEAgUNAAAAAAAAAAAAAAECAwQFBhESExQWIlFyFSE0NUFSU2FxkqHB0f/EABgBAAMBAQAAAAAAAAAAAAAAAAADBQQG/8QAIxEAAgIBAAsAAAAAAAAAAAAAAAECERIEEyIxMjRRYYGh8P/aAAwDAQACEQMRAD8A3sIqYVXWb7ERNFw7BOqLDnYTJJGdkoTfnOxfZiVaQsZSk8bdlk1dNpe2hs4jEtvyUkzuadxn6DkspdlVcL0iJ5OcbiHDz1tZhII72PD2kd/kRrB9c0nzeTMSmzi20oS61D5Vzuezh3/owt7L62dVGD1sUqwx3V8vZ6jrS/4DYCrq7H9xPuAPuZIx0fsWq06WxwCrSnWyOEwAD4wjy/g3IAAeTD//2Q==';
	/** @var string */
	public $cssClWarning='.capslock-warning {
	display: none;
	position: absolute;
	left: 100%%;
	top: 0;
	width: 17px;
	height: 17px;
	margin: 4px 0 0 -23px;
	text-indent: -100em;
	background: url(%s) no-repeat;
	}';
	/** @var string */
	public $cbLabel='Show Password';
	/** @var string */
	public $cbDesc='Show the password as plain text (not advisable in a public place)';
	/** @var string */
	public $icoShowPswd='data:image/png;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAKAAoDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAABwX/xAAkEAACAQMDAwUAAAAAAAAAAAABAgMABBEFBhITIWEiIzFCUf/EABUBAQEAAAAAAAAAAAAAAAAAAAUG/8QAHhEAAQMEAwAAAAAAAAAAAAAAAgABAwQFETGB4fD/2gAMAwEAAhEDEQA/AD3au14LTacOnah0lm1hJHuQwHNAFzGF8qfUfJ70XXO29WguZYTYzuY3KckUkHBxkH8pUS4maJC0shIUYJY1Olurjqv78vyfuamIbhLGRFvPuuEmVKDsy//Z';
	/** @var string */
	public $cssShowPswd='label.show-password {
	white-space: nowrap;
	font-size: 0.95em;
	margin: 0 0 0 8px;
	padding: 0 15px 0 0;
	letter-spacing: -0.05em;
	background: url(%s) no-repeat 100%% 60%%;
	}';
	/**
	 * masking char
	 * @var string
	 */
	public $symbol='u25cf'; //'●'
	/**
	 * reset control on pageload
	 * @var bool
	 */
	public $rstMasked=TRUE;


	/**
	 * @param string $label
	 * @param int $cols width of the control
	 * @param int $maxLength maximum number of characters the user may enter
	 */
	public function __construct($label=NULL, $cols=NULL, $maxLength=NULL)
	{
		parent::__construct($label, $cols, $maxLength);
		$this->setPasswordMode();
		$this->container=Html::el();
	}

	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets control's value.
	 * @param mixed $value
	 * @return PswdInput provides a fluent interface
	 */
	public function setValue($value)
	{
		parent::setValue($value);
		return $this;
	}

	/**
	 * @return Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
		$id=$control->id;
		$container=Html::el('span', array('style' => 'position: relative; float: left;'))
					->add($control);
		$style=Html::el('style');
		if ($this->useClWarning && !$this->useMasked) {
			$style->add(sprintf($this->cssClWarning, $this->icoClWarning));
			}
		if ($this->useShowPswd && !$this->useMasked) {
			$style->add(sprintf($this->cssShowPswd, $this->icoShowPswd));
			}
		if (!$this->useMasked) {
			$container->add($style);
			}

		$data=array();
		if ($this->useClWarning && !$this->useMasked) {
			$data['clwarning']=array(
					'str' => $this->strClWarning,
					);
			}
		if ($this->useShowPswd && !$this->useMasked) {
			$data['showpswd']=array(
					'cb' => array(
						'label' => $this->cbLabel,
						'desc' => $this->cbDesc
						)
					);
			}
		if ($this->useMasked) {
			$data['masked']=array(
					'symbol' => Json::decode('"\\'.$this->symbol.'"'),
					'reset' => $this->rstMasked
					);
			}
		if (count($data)) {
			$fid=array();
			$data['fid']=$this->getForm()->getElementPrototype()->id;
			$container->add(Html::el('script', array('type' => 'text/javascript'))
							->add("/* <![CDATA[ */\n$('#$id').ready(PswdInput('$id', ".TemplateHelpers::escapeJs($data)."));\n/* ]]> */")
							);
			}
		return $container;
	}

	/**
	 * @param string $caption
	 * @return Html
	 */
	public function getLabel($caption=NULL)
	{
		return parent::getLabel($caption);
	}
}
