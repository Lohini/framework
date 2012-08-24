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
	/** @var bool use CAPS-lock warning */
	public $useClWarning=TRUE;
	/** @var bool use Show Password Checkbox */
	public $useShowPswd=FALSE;
	/** @var bool use masking */
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
	public $icoShowPswd='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAU5JREFUeNpsjUtLAmEUht9vvDDjJW2azGvSootSrQIjkDYGQmMILQslClob9Q8iaOc62rVsWxgt/Af9hajoZkVjojnOzDdfk1IYdODlPZzzvOcAfXWyD5Fdx8rsJlYuyBD7d9xP4+aBqTFBRjQXREgO7hZ52cX/A65lEZ9bms5UL95QrdQwk05mvmd/wAE3yE5RyMMTdp5fPuOs8gg4RGdpnc97XSC/YCGL5OTseAqaitGRdleoK0hMRFIby0h0QckH29aqx7rm5/D5gdBgGyFfDzRVcJsrQl7yw2Y73sP84kI4Bw3ErDeAtgpJ6EB0atBbGiQvCcSG1XvycCocStFwnFAKh9PA9oEJZpo4Kmlo1Q0wg0LpqHf2ZoNE7bUmqKaDYyYKaQu0Qk+3FIZGrTcUTQMRu/LeudJVGjApAxjDEN/zVwVdJ2Bo6Xj5EmAAZKeAFvt4RVYAAAAASUVORK5CYII=';
	/** @var string */
	public $cssShowPswd='label.show-password {
	white-space: nowrap;
	font-size: 0.95em;
	margin: 0 0 0 8px;
	padding: 0 15px 0 0;
	letter-spacing: -0.05em;
	background: url(%s) no-repeat 100%% 60%%;
	}';
	/** @var string masking char */
	public $symbol='â—Ź';
	/** @var bool reset control on pageload */
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
	 * Returns control's value
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Sets control's value
	 * @param mixed $value
	 * @return PswdInput provides a fluent interface
	 */
	public function setValue($value)
	{
		parent::setValue($value);
		return $this;
	}

	/**
	 * Returns control's container prototype
	 * @return Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
	}

	/**
	 * Generates control's HTML element
	 * @return Html
	 */
	public function getControl()
	{
		$control=parent::getControl();
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
		if ($this->useMasked) {
			$data['masked']=array(
					'symbol' => $this->symbol,
					'reset' => $this->rstMasked
					);
			}
		else {
			if ($this->useClWarning) {
				$data['clwarning']=array(
						'str' => $this->translate($this->strClWarning)
						);
				}
			if ($this->useShowPswd) {
				$data['showpswd']=array(
						'cb' => array(
							'label' => $this->translate($this->cbLabel),
							'desc' => $this->translate($this->cbDesc)
							)
						);
				}
			}
		if (count($data)) {
			$data['fid']=$this->getForm()->getElementPrototype()->id;
			$container->add(
					Html::el(
						'script',
						array('type' => 'text/javascript')
						)
						->add("head.ready(function() {head.js(
							'".rtrim($this->form->getPresenter(FALSE)->getContext()->getService('httpRequest')->getUrl()->getBasePath(), '/')."/js/PswdInput.js',
							function() {
								PswdInput('{$control->id}', ".\Nette\Templating\tHelpers::escapeJs($data).');
								}
							);});')
					);
			}
		return $container;
	}
}
