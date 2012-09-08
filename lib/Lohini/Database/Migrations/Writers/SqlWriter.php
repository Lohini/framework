<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Writers;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class SqlWriter
extends \Lohini\Database\Migrations\QueryWriter
{
	/**
	 * @param string $version
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct($version, \Lohini\Packages\Package $package)
	{
		parent::__construct($version, $package);
		$this->file=$this->dir.'/'.$this->version.'.sql';
	}

	/**
	 * @param array $sqls
	 * @return bool
	 */
	public function write(array $sqls)
	{
		if (!$sqls) {
			return FALSE;
			}

		foreach ($sqls as $sql) {
			$this->writeSql($sql);
			}

		return (bool)count($sqls);
	}

	/**
	 * @param string $sql
	 */
	private function writeSql($sql)
	{
		if (!file_exists($this->file)) {
			touch($this->file);
			}

		file_put_contents($this->file, "$sql;\n", FILE_APPEND);
	}
}
