<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Extensions\Compass\Functions;
/**
 * Compass extension SassScript constants functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */
 
/**
 * Compass extension SassScript constants functions class.
 * A collection of functions for use in SassSCript.
 */
class Constants
{
	public static function opposite_position($pos) {
		$opposites=array();
		foreach (explode(' ', $pos->toString()) as $position) {
			switch (trim($position)) {
				case 'top':
					$opposites[]='bottom';
					break;
				case 'right':
					$opposites[]='left';
					break;
				case 'bottom':
					$opposites[]='top';
					break;
				case 'left':
					$opposites[]='right';
					break;
				case 'center':
					$opposites[]='center';
					break;
				default:
					throw new \Exception('Cannot determine the opposite of '.trim($position));
				}
			}
		return new \Lohini\WebLoader\Filters\Sass\Script\Literals\String(join(' ', $opposites));
	}
}
