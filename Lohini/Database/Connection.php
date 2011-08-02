<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database;

/**
 * Lohini Connection
 *
 * @author Lopo <lopo@lohini.net>
 */
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
		if (!isset($options->databases->common->primary)) {
			throw new \RuntimeException("Primary database isn't set (databases.primary)");
			}
		self::$primary=$options->databases->common->primary;
		foreach ($options->databases as $dbk => $dbc) {
			if ($dbk=='common') {
				continue;
				}
			if (is_a($dbc, '\Nette\ArrayHash')) {
				if (\Nette\Utils\Strings::startsWith($dbc->driver, 'pdo_')) {
					continue;
					}
				$c=\dibi::connect($dbc, $dbk);
				if (
					($cp=
						isset($dbc->profiler)
							? $dbc->profiler
							: (isset($options->databases->common->profiler)
								? $options->databases->common->profiler
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
			self::initialize(\Nette\Environment::getConfig());
			}
		return \dibi::getConnection($name!==NULL? $name : self::$primary);
	}
}
