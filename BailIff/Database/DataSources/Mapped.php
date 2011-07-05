<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\DataSources;
/**
 * @author Michael Moravec
 * @author Štěpán Svoboda
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Base class for Doctrine2 based data sources
 */
abstract class Mapped
extends DataSource
{
	/**
	 * @var array Alias to column mapping
	 */
	protected $mapping=array();


	/**
	 * Gets columns mapping
	 * @param array
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Sets columns mapping
	 * @param array
	 */
	public function setMapping(array $mapping)
	{
		$this->mapping=$mapping;
	}

	/**
	 * Does datasource have column of given name?
	 * @return bool
	 */
	public function hasColumn($name)
	{
		return array_key_exists($name, $this->mapping);
	}

	/**
	 * Gets list of column aliases
	 * @return array
	 */
	public function getColumns()
	{
		return array_keys($this->mapping);
	}
}
