<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Database;

use BailIff\Environment;

class Connection
extends \DibiConnection
{
	/** @var string */
	protected static $primary;
	/** @var bool */
	protected static $initialized=FALSE;


	/**
	 * Initialize database connections
	 * @param string $name
	 * @throws \RuntimeException
	 */
	protected static function initialize($options)
	{
		if (self::$initialized) {
			return;
			}
		if (!isset($options->databases->primary)) {
			throw new \RuntimeException("Primary database isn't set (databases.primary)");
			}
		self::$primary=$options->databases->primary;
		foreach ($options->database as $dbk => $dbc) {
			if (is_a($dbc, 'Nette\ArrayHash')) {
				$c=\dibi::connect($dbc, $dbk);
				if (
					($cp=
						isset($dbc->profiler)
							? $dbc->profiler
							: (isset($options->databases->profiler)
								? $options->databases->profiler
								: FALSE
								)
						)!==FALSE
					&& $cp!=0
					) {
					$profiler= (is_numeric($cp) || is_bool($cp))
							? new \DibiProfiler(array('explain' => TRUE))
							: new $cp
							;
					$profiler->setFile(VAR_DIR."/log/db_$dbk.log");
					$c->setProfiler($profiler);
					}
				}
			}
		self::$initialized=TRUE;
	}

	/**
	 * @param string $name
	 * @return \DibiConnection
	 */
	public static function getConnection($name=NULL)
	{
		if (!self::$initialized) {
			self::initialize(Environment::getConfig());
			}
		return \dibi::getConnection($name!==NULL? $name : self::$primary);
	}
}
