<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass;
/**
 * Compass extension SassScript colour stop objects and functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Compass extension List object.
 */
class CompassList
extends \Lohini\WebLoader\Filters\Sass\Script\Literals\Literal
{
	public function __construct($values)
	{
		$this->value=$values;
	}

	public function getValues()
	{
		return $this->value;
	}

	/**
	 * Returns the type of this
	 * @return string the type of this
	 */
	protected function getTypeOf()
	{
		return 'list';
	}

	/**
	 * @return string
	 */
	public function toString()
	{
		$values=array();
		foreach ($this->value as $value) {
			$values[]=$value->toString();
			}
		return join(', ', $values);
	}

	public static function isa($subject) {}
}
