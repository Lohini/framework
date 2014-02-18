<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini;

/**
 * The Lohini Framework
 *
 * @author Lopo <lopo@lohini.net>
 */
final class Framework
{
	/**#@+ Lohini version ID's */
	const NAME='Lohini Framework';
	const VERSION='0.4.0-dev';
	const REVISION='$WCREV$ released on $WCDATE$';
	/**#@-*/

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException;
	}
}
