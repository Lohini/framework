<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Database;

class Connection
extends \DibiConnection
{
	/** @var string */
	protected static $primary;


	/**
	 * Initialize database connections
	 * @param string $name
	 * @return Connection
	 * @throws \RuntimeException
	 */
	static public function initialize($options)
	{
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
	}

	/**
	 * @param string $name
	 * @return \DibiConnection
	 */
	static public function getConnection($name=NULL)
	{
		return \dibi::getConnection($name!==NULL? $name : self::$primary);
	}
}
