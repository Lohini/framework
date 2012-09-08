<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Templating;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Utils\Filesystem,
	Lohini\Templating\EditableTemplates,
	Lohini\Templating\TemplateSource;

/**
 */
class EditableTemplatesTest
extends \Lohini\Testing\OrmTestCase
{
	/** @var \Lohini\Caching\LatteStorage */
	private $storage;
	/** @var \Lohini\Database\Doctrine\Dao */
	private $dao;
	/** @var \Lohini\Templating\EditableTemplates */
	private $templates;


	public function setUp()
	{
		$this->createOrmSandbox(array('Lohini\Templating\TemplateSource'));

		$cacheDir=$this->getContext()->expand('%tempDir%/cache');
		if ($nsDirs=glob($cacheDir.'/*'.EditableTemplates::CACHE_NS.'*')) {
			Filesystem::rmDir(reset($nsDirs));
			}

		$this->storage=$this->getContext()->createInstance('Lohini\Caching\LatteStorage', array($cacheDir));
		$this->templates=new EditableTemplates($this->getDoctrine(), $this->storage);

		$this->dao=$this->getDao('Lohini\Templating\TemplateSource');
	}

	public function testSavedTemplateHasAFile()
	{
		$template=new TemplateSource;
		$template->setSource('{$name}');

		$this->templates->save($template);
		$this->assertNotNull($id=$template->getId());
		$this->getEntityManager()->flush();

		$template=$this->dao->getReference($id);
		$file=$this->templates->getTemplateFile($template);

		$this->assertEquals($template->getSource(), static::readTemplate($file));
	}

	public function testFileWillBeRestoredWhenDeleted()
	{
		$template=new TemplateSource;
		$template->setSource('{$name}');

		$this->templates->save($template);
		$file=$this->templates->getTemplateFile($template);
		$this->assertFileExists($file);

		Filesystem::rm($file);
		$this->assertFileNotExists($file);

		$file=$this->templates->getTemplateFile($template);
		$this->assertFileExists($file);
	}

	/**
	 * @group one
	 */
	public function testTemplateCanBeExtended()
	{
		$layout=new TemplateSource;
		$layout->setSource('<div>{include #content}</div>');

		$template=new TemplateSource;
		$template->setSource('{block #content}{$name}{/block}');
		$template->setExtends($layout);

		$this->templates->save($template);
		$this->assertNotNull($id=$template->getId());
		$this->assertNotNull($layout->getId());
		$this->getEntityManager()->flush();

		$template=$this->dao->getReference($id);
		$file=$this->templates->getTemplateFile($template);

		$this->assertStringMatchesFormat(
			"{extends %s}\n".$template->getSource(),
			static::readTemplate($file)
			);
	}

	/**
	 * @param string $file
	 * @return string
	 */
	private static function readTemplate($file)
	{
		ob_start();
		\Nette\Utils\LimitedScope::evaluate(file_get_contents($file));
		return ob_get_clean();
	}
}
