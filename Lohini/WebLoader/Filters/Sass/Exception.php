<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * Sass exception.
 * @author                      Chris Yates <chris.l.yates@gmail.com>
 * @copyright   Copyright (c) 2010 PBM Web Development
 * @license                     http://phamlp.googlecode.com/files/license.txt
 * @package                     PHamlP
 * @subpackage  Sass
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Sass exception class
 */
class Exception
extends \Exception
{
	/**
	 * @param string $message Exception message
	 * @param object $object object with source code and meta data
	 */
	public function __construct($message, $object=NULL, $type=NULL)
	{
		parent::__construct($message.(is_object($object)? ": $object->filename::$object->line\nSource: $object->source" : ''), $type);
	}
}
