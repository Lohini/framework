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

use BailIff\Components\DataGrid\Action;

/**
 * Representation of data grid action column.
 * If you want to write your own implementation you must inherit this class.
 */
class ActionColumn
extends Column
implements \ArrayAccess
{
	/**
	 * @param string $caption column's textual caption
	 */
	public function __construct($caption='Actions')
	{
		parent::__construct($caption);
		$this->addComponent(new \Nette\ComponentModel\Container, 'actions');
		$this->removeComponent($this->getComponent('filters'));
		$this->orderable=FALSE;
	}

	/**
	 * Has column filter box?
	 * @return bool
	 */
	public function hasFilter()
	{
		return FALSE;
	}

	/**
	 * Returns column's filter
	 * @param bool $need throw exception if component doesn't exist?
	 * @return BailIff\Components\DataGrid\Filters\IColumnFilter|NULL
	 * @throws \Nette\InvalidStateException
	 */
	public function getFilter($need=TRUE)
	{
		if ($need==TRUE) {
			throw new \Nette\InvalidStateException('DataGrid\Columns\ActionColumn cannot has filter.');
			}
		return NULL;
	}

	/**
	 * Action factory
	 * @param string $title textual title
	 * @param string $signal textual link destination
	 * @param Html $icon element which is added to a generated link
	 * @param bool $useAjax ? (add class self::$ajaxClass into generated link)
	 * @param bool $type generate link with argument? (variable $keyName must be defined in data grid)
	 * @return \BailIff\Components\DataGrid\Action
	 */
	public function addAction($title, $signal, $icon=NULL, $useAjax=FALSE, $type=Action::WITH_KEY)
	{
		$action=new Action($title, $signal, $icon, $useAjax, $type);
		$this[]=$action;
		return $action;
	}

	/**
	 * Does column has any action?
	 * @return bool
	 */
	public function hasAction($type=NULL)
	{
		return count($this->getActions($type))>0;
	}

	/**
	 * Returns column's action specified by name
	 * @param string $name action's name
	 * @param bool $need throw exception if component doesn't exist?
	 * @return \Nette\ComponentModel\IComponent|NULL
	 * @todo return Component
	 */
	public function getAction($name=NULL, $need=TRUE)
	{
		return $this->getComponent('actions')->getComponent($name, $need);
	}

	/**
	 * Iterates over all column's actions
	 * @param string $type
	 * @return \ArrayIterator|NULL
	 */
	public function getActions($type='BailIff\Components\DataGrid\IAction')
	{
		$actions=new \ArrayObject();
		foreach ($this->getComponent('actions')->getComponents(FALSE, $type) as $action) {
			$actions->append($action);
			}
		return $actions->getIterator();
	}

	/**
	 * Formats cell's content
	 * @param mixed $value
	 * @param \DibiRow|array
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	public function formatContent($value, $data=NULL)
	{
		throw new \Nette\InvalidStateException('DataGrid\Columns\ActionColumn cannot be formated.');
	}

	/**
	 * Filters data source
	 * @param mixed
	 * @throws \Nette\InvalidStateException
	 */
	public function applyFilter($value)
	{
		throw new \Nette\InvalidStateException('DataGrid\Columns\ActionColumn cannot be filtered.');
	}

	/*	 * ******************* interface \ArrayAccess ******************** */
	/**
	 * Adds the component to the container
	 * @param string $name component name
	 * @param \Nette\ComponentModel\IComponent
	 * @throws \InvalidArgumentException
	 */
	final public function offsetSet($name, $component)
	{
		if (!$component instanceof \Nette\ComponentModel\IComponent) {
			throw new \InvalidArgumentException('DataGrid\Columns\ActionColumn accepts only \Nette\ComponentModel\IComponent objects.');
			}
		$this->getComponent('actions')->addComponent($component, $name==NULL? count($this->getActions()) : $name);
	}

	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param string $name component name
	 * @return \Nette\ComponentModel\IComponent
	 */
	final public function offsetGet($name)
	{
		return $this->getAction((string)$name, TRUE);
	}

	/**
	 * Does component specified by name exists?
	 * @param string $name component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getAction($name, FALSE)!==NULL;
	}

	/**
	 * Removes component from the container. Throws exception if component doesn't exist.
	 * @param string $name component name
	 */
	final public function offsetUnset($name)
	{
		$component=$this->getAction($name, FALSE);
		if ($component!==NULL) {
			$this->getComponent('actions')->removeComponent($component);
			}
	}
}
