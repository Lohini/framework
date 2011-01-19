<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Database;

use BailIff\Environment;

class Connection
extends \DibiConnection
{
	/** @var string */
	protected static $primary;


	/**
	 * Initialize database connections
	 * @param string $name
	 * @throws \RuntimeException
	 * @return Connection
	 */
	static public function initialize()
	{
		$gconf=Environment::getConfig('databases');
		if (!isset($gconf->primary)) {
			throw new \RuntimeException("Primary database isn't set (databases.primary)");
			}
		self::$primary=$gconf->primary;
		foreach (Environment::getConfig('database') as $dbk => $dbc) {
			if (is_a($dbc, 'Nette\Config\Config')) {
				$c=\dibi::connect($dbc, $dbk);
				if (($cp= isset($conf->profiler)? $conf->profiler : (isset($gconf->profiler)? $gconf->profiler : FALSE))!==FALSE && $cp!=0) {
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
	 * @return DibiConnection
	 */
	static public function getConnection($name=NULL)
	{
		return \dibi::getConnection($name!==NULL? $name : self::$primary);
	}
}
