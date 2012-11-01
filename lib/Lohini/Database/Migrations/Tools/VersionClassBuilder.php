<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Migrations\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class VersionClassBuilder
extends \Nette\Object
{
	/** @var \Nette\PhpGenerator\ClassType */
	private $class;
	/** @var \Lohini\Packages\Package */
	private $package;


	/**
	 * @param \Lohini\Packages\Package $package
	 * @param string $name
	 */
	public function __construct(\Lohini\Packages\Package $package, $name=NULL)
	{
		$this->package=$package;
		$this->class=new \Nette\PhpGenerator\ClassType($name ?: 'Version'.date('YmdHis'));
		$this->class->addExtend('Lohini\Database\Migrations\AbstractMigration');
		$this->class->addDocument('@todo: write description of migration');

		$up=$this->class->addMethod('up');
		$up->addParameter('schema')->setTypeHint('Schema');
		$up->addBody("// this method was auto-generated, please modify it to your needs\n");
	}

	/**
	 * @param string $sql
	 * @param array $params
	 */
	public function addUpSql($sql, array $params=array())
	{
		/** @var \Nette\PhpGenerator\Method $up */
		$up=$this->class->methods['up'];
		$up->addBody('$this->addSql(?,?)', array($sql, $params));
	}

	/**
	 * @param string $sql
	 * @param array $params
	 */
	public function addDownSql($sql, array $params=array())
	{
		if (!isset($this->class->methods['down'])) {
			$down=$this->class->addMethod('down');
			$down->addParameter('schema')->setTypeHint('Schema');
			$down->addBody("// this method was auto-generated, please modify it to your needs\n");
			}

		/** @var \Nette\PhpGenerator\Method $down */
		$down = $this->class->methods['down'];
		$down->addBody('$this->addSql(?,?)', array($sql, $params));
	}

	/**
	 * @return string
	 */
	public function build()
	{
		$s='namespace '.$this->package->getNamespace().'\Migration;'."\n\n"
			.'use Doctrine\DBAL\Schema\Schema;'."\n"
			.'use Lohini;'."\n"
			.'use Nette;'."\n";

		return '<?php'."\n\n$s\n\n\n".(string)$this->class;
	}
}
