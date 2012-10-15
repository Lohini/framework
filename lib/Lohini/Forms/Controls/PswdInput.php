<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
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
		'clWarning' => NULL,
		'cbLabel' => NULL,
		'cbDesc' => NULL,
		'symbol' => NULL
		);


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
	 * Returns control's value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets value of a property.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws \Lohini\MemberAccessException
	 */
	public function __set($name, $value)
	{
		$name=(string)$name;
		if (!strlen($name)) {
			throw \Lohini\MemberAccessException::propertyWriteWithoutName($name);
			}
		if (!array_key_exists($name, $this->data)) {
			throw \Lohini\MemberAccessException::propertyNotWritable('an undeclared', $this, $name);
			}
		$this->data[$name]=$value;
	}

	/**
	 * Returns property value.
	 *
	 * @param string $name
	 * @return mixed property value
	 * @throws \Lohini\MemberAccessException
	 */
	public function &__get($name)
	{
		$name=(string)$name;
		if (!strlen($name)) {
			throw \Lohini\MemberAccessException::propertyReadWithoutName($name);
			}
		if (!array_key_exists($name, $this->data)) {
			throw \Lohini\MemberAccessException::propertyNotReadable('an undeclared', $this, $name);
			}
		return $this->data[$name];
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
			\Lohini\Utils\Json::encode($data)
			);
		return Html::el('span', array('style' => 'position: relative; float: left;'))
				->add($control);
	}
}
