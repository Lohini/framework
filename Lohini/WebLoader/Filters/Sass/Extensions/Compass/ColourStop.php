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

use Lohini\WebLoader\Filters\Sass\Script\Literals;

class ColourStop
extends Literals\Literal
{
	private $colour;
	public $stop;


	public function __construct($colour, $stop=NULL)
	{
		$this->colour=$colour;
		$this->stop=$stop;
	}

	protected function getColor()
	{
		return $this->getColour();
	}

	protected function getColour()
	{
		return $this->colour;
	}

	public function toString()
	{
		$s=$this->colour->toString();
		if (!empty($this->stop)) {
			$s.=' ';
			if ($this->stop->isUnitless()) {
				$s.=$this->stop->op_times(new Literals\Number('100%'))->toString();
				}
			else {
				$s.=$this->stop->toString();
				}
			}
		return $s;
	}

	public static function isa($subject) {}
}
