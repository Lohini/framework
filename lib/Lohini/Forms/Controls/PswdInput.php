<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Forms\Controls;

use Nette\Utils\Html;

/**
 * Improved password input control
 * - CAPS-LOCK warning
 * - show password checkbox
 * - masked password
 *
 * @author Lopo <lopo@lohini.net>
 */
class PswdInput
extends \Nette\Forms\Controls\TextInput
{
	/** @var Html */
	protected $container;
	/** @var array */
	private $data=array(
		'useWarning' => TRUE,
		'useShow' => FALSE,
		'useMasked' => FALSE,
		'clWarning' => 'Caps-lock is ON!',
		'cbLabel' => 'Show Password',
		'cbDesc' => 'Show the password as plain text (not advisable in a public place)',
		'symbol' => "●"
		);


	/**
	 * @param string $label
	 * @param int $maxLength maximum number of characters the user may enter
	 */
	public function __construct($label=NULL, $maxLength=NULL)
	{
		parent::__construct($label, $maxLength);
		$this->setType('password');
		$this->container=Html::el();
	}

	/**
	 * Returns control's value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Get/set data
	 *
	 * @param string $name
	 * @param mixed $val
	 * @retun mixed
	 */
	public function data($name, $value=NULL)
	{
		$name=(string)$name;
		if (!strlen($name) || !isset($this->data[$name])) {
			throw new \Nette\InvalidArgumentException;
			}
		if ($value!==NULL) {
			$this->data[$name]=$value;
			}
		else {
			return $this->data[$name];
		}
	}

	/**
	 * Returns control's container prototype
	 *
	 * @return Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element
	 *
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl()
				->addClass('pswdinput');
		$this->data['fid']=$this->getForm()->getElementPrototype()->id;
		$data=array_filter($this->data);
		$control->data(
			'lohini-pswd',
			\Nette\Utils\Json::encode($data)
			);
		return Html::el('span', array('style' => 'position: relative; float: left;'))
				->add($control);
	}
}
