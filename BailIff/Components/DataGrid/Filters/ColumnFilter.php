<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\DataGrid\Filters;
/**
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Base class that implements the basic common functionality to data grid column's filters
 */
abstract class ColumnFilter
extends \Nette\ComponentModel\Component
implements IColumnFilter
{
	/** @var \Nette\Forms\Controls\BaseControl form element */
	protected $element;
	/** @var string value of filter (if was filtered) */
	protected $value;


	public function __construct()
	{
		parent::__construct();
	}

	/*	 * ******************* interface DataGrid\Filters\IColumnFilter ******************** */
	public function getFormControl()
	{
	}

	/**
	 * Gets filter's value, if was filtered
	 * @return string
	 */
	public function getValue()
	{
		$dataGrid=$this->lookup('BailIff\Components\DataGrid\DataGrid', TRUE);

		// set value if was data grid filtered yet
		parse_str($dataGrid->filters, $list);
		foreach ($list as $key => $value) {
			if ($key==$this->getName()) {
				$this->setValue($value);
				break;
				}
			}
		return $this->value;
	}

	/**
	 * Sets filter's value
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->getFormControl()->setDefaultValue($value);
		$this->value=$value;
	}
}
