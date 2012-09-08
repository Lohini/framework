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
class ClassWriter
extends \Lohini\Database\Migrations\QueryWriter
{
	/** @var string */
	private $namespace;
	/** @var \Lohini\Utils\MethodAppend */
	private $methodWriter;


	/**
	 * @param string $version
	 * @param \Lohini\Packages\Package $package
	 */
	public function __construct($version, \Lohini\Packages\Package $package)
	{
		parent::__construct($version, $package);
		$this->namespace=$this->package->getNamespace().'\\Migration';
		$this->file=$this->dir.'/'.$this->version.'.php';
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

		foreach ($sqls as $sql){
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
			$versionClass=new \Lohini\Database\Migrations\Tools\VersionClassBuilder($this->package, $this->version);
			file_put_contents($this->file, $versionClass->build());
			}

		if ($this->methodWriter===NULL) {
			require_once $this->file;
			$class=new \Nette\Reflection\ClassType($this->namespace.'\\'.$this->version);
			$this->methodWriter=new \Lohini\Utils\MethodAppend($class->getMethod('up'));
			}

		$this->methodWriter->append('$this->addSql('.static::varDump($sql).');');
	}

	/**
	 * @author David Grudl
	 * @see Nette\Utils\PhpGenerator\Helpers::_dump
	 *
	 * @param string $var
	 * @return string
	 */
	private static function varDump($var)
	{
		static $table;
		if ($table===NULL) {
			foreach (range("\x00", "\xFF") as $ch) {
				$table[$ch]=ord($ch)<32 || ord($ch)>=127
					? '\\x'.str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT)
					: $ch;
				}
			$table["\r"]='\r';
			$table["\n"]='\n';
			$table["\t"]='\t';
			$table['$']='\\$';
			$table['\\']='\\\\';
			$table['"']='\\"';
			}
		return '"'.strtr($var, $table).'"';
	}
}
