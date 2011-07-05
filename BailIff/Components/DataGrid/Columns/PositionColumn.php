<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\Columns;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

use BailIff\Database\DataSources\IDataSource,
	Nette\Utils\Html;

/**
 * Representation of positioning data grid column, that provides moving entries up or down
 */
class PositionColumn
extends NumericColumn
{
	/** @var array */
	public $moves=array();
	/** @var string  signal handler of move action */
	public $destination;
	/** @var bool */
	public $useAjax;
	/** @var int */
	protected $min;
	/** @var int */
	protected $max;


	/**
	 * Checkbox column constructor
	 * @param string $caption column's textual caption
	 * @param string $destination destination or signal to handler which do the move rutine
	 * @param array $moves textual labels for generated links
	 * @param bool $useAjax ? (add class self::$ajaxClass into generated link)
	 */
	public function __construct($caption=NULL, $destination=NULL, array $moves=NULL, $useAjax=TRUE)
	{
		parent::__construct($caption, 0);

		$this->useAjax=$useAjax;

		if (empty($moves)) {
			$this->moves['up']='Move up';
			$this->moves['down']='Move down';
			}
		else {
			$this->moves=$moves;
			}

		// try set handler if is not set
		if ($destination===NULL) {
			$this->destination=$this->getName.'Move!';
			}
		else {
			$this->destination=$destination;
			}

		$this->monitor('BailIff\Components\Datagrid\DataGrid');
	}

	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param \Nette\ComponentModel\IComponent $component (DataGrid)
	 */
	protected function attached($component)
	{
		if ($component instanceof \BailIff\Components\DataGrid\DataGrid) {
			$dataSource=clone $component->dataSource;
			$this->min= $this->max= 0;
			$first=$dataSource->sort($this->getName(), IDataSource::ASCENDING)->reduce(1)->fetch();
			if (count($first)>0) {
				$this->min=(int)$first[0][$this->getName()];
				}
			$last=$dataSource->sort($this->getName(), IDataSource::DESCENDING)->reduce(1)->fetch();
			if (count($last)>0) {
				$this->max=(int)$first[0][$this->getName()];
				}
			}

		parent::attached($component);
	}

	/**
	 * Formats cell's content
	 * @param int $value
	 * @param \DibiRow|array $data
	 * @return string
	 */
	public function formatContent($value, $data=NULL)
	{
		$control=$this->getDataGrid()->lookup('Nette\Application\UI\Control', TRUE);
		$uplink=$control->link($this->destination, array('key' => $value, 'dir' => 'up'));
		$downlink=$control->link($this->destination, array('key' => $value, 'dir' => 'down'));

		$up=Html::el('a')->title($this->moves['up'])->href($uplink)->add(Html::el('span')->class('up'));
		$down=Html::el('a')->title($this->moves['down'])->href($downlink)->add(Html::el('span')->class('down'));

		if ($this->useAjax) {
			$up->class(self::$ajaxClass);
			$down->class(self::$ajaxClass);
			}

		// disable top up & top bottom links
		if ($value==$this->min) {
			$up->href(NULL);
			$up->class('inactive');
			}
		if ($value==$this->max) {
			$down->href(NULL);
			$down->class('inactive');
			}

		$positioner=Html::el('span')->class('positioner')->add($up)->add($down);
		return $positioner.$value;
	}
}
