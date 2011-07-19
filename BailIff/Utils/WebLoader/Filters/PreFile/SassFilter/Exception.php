<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * Sass exception.
 * @author                      Chris Yates <chris.l.yates@gmail.com>
 * @copyright   Copyright (c) 2010 PBM Web Development
 * @license                     http://phamlp.googlecode.com/files/license.txt
 * @package                     PHamlP
 * @subpackage  Sass
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
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
	public function __construct($message, $object=NULL)
	{
		parent::__construct($message.(is_object($object)? ": {$object->filename}::{$object->line}\nSource: {$object->source}" : ''));
	}
}
