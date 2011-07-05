<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use Nette\Utils\Html;

/**
 * Representation of data grid action.
 */
class Action
extends \Nette\ComponentModel\Component
implements IAction
{
	/** #@+ special action key */
	const WITH_KEY=TRUE;
	const WITHOUT_KEY=FALSE;
	/** #@- */

	/** @var \Nette\Utils\Html action element template */
	protected $html;
	/** @var string */
	static public $ajaxClass='datagrid-ajax';
	/** @var string */
	public $destination;
	/** @var bool|string */
	public $key;
	/** @var Callback|Closure */
	public $ifDisableCallback;


	/**
	 * Data grid action constructor
	 * @note for full ajax support, destination should not change module,
	 * @note presenter or action and must be ended with exclamation mark (!)
	 *
	 * @param string $title textual title
	 * @param string $destination textual link destination
	 * @param \Nette\Utils\Html $icon element which is added to a generated link
	 * @param bool use ajax? (add class self::$ajaxClass into generated link)
	 * @param mixed generate link with argument? (if yes you can specify name of parameter
	 * 				   otherwise variable DataGrid\DataGrid::$keyName will be used and must be defined)
	 */
	public function __construct($title, $destination, Html $icon=NULL, $useAjax=FALSE, $key=self::WITH_KEY)
	{
		parent::__construct();
		$this->destination=$destination;
		$this->key=$key;

		$a=Html::el('a')->title($title);
		if ($useAjax) {
			$a->addClass(self::$ajaxClass);
			}

		if ($icon!==NULL && $icon instanceof Html) {
			$a->add($icon);
			}
		else {
			$a->setText($title);
			}
		$this->html=$a;
	}

	/**
	 * Generates action's link. (use before data grid is going to be rendered)
	 *
	 * @param array $args
	 */
	public function generateLink(array $args=NULL)
	{
		$dataGrid=$this->lookup('BailIff\Components\DataGrid\DataGrid', TRUE);
		$control=$dataGrid->lookup('Nette\Application\UI\Control', TRUE);

		switch ($this->key) {
			case self::WITHOUT_KEY:
				$link=$control->link($this->destination);
				break;
			case self::WITH_KEY:
			default:
				$key= $this->key==NULL || is_bool($this->key)? $dataGrid->keyName : $this->key;
				$link=$control->link($this->destination, array($key => $args[$dataGrid->keyName]));
				break;
			}

		$this->html->href($link);
	}

	/*	 * ******************* interface DataGrid\IAction ******************** */
	/**
	 * Gets action element template
	 * @return \Nette\Utils\Html
	 */
	public function getHtml()
	{
		return $this->html;
	}
}
