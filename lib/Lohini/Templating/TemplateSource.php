<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM,
	Nette\Caching\Cache,
	Nette\Utils\PhpGenerator;

/**
 * @ORM\Entity()
 * @ORM\Table(name="templates")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "TemplateSource"})
 *
 * @method string getSource()
 */
class TemplateSource
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	protected $name;
	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	protected $description;
	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	protected $source;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $layout = FALSE;
	/**
	 * @ORM\ManyToOne(targetEntity="TemplateSource", cascade={"persist"})
	 * @ORM\JoinColumn(name="extends_id", referencedColumnName="id")
	 * @var \Lohini\Templating\TemplateSource
	 */
	private $extends;


	/**
	 * @param TemplateSource $extends
	 */
	public function setExtends(TemplateSource $extends=NULL)
	{
		$this->extends=$extends;
	}

	/**
	 * @return TemplateSource
	 */
	public function getExtends()
	{
		return $this->extends;
	}

	/**
	 * @param \Lohini\Templating\EditableTemplates $templates
	 * @param array $db
	 * @param string $layoutFile
	 * @return string
	 */
	public function build(EditableTemplates $templates, array &$db, $layoutFile=NULL)
	{
		$source=$this->source;

		$dp[Cache::TAGS][]='dbTemplate#'.$this->getId();

		// todo: debugging only?
		$db[Cache::FILES][]=self::getReflection()->getFileName();
		$db[Cache::FILES][]=EditableTemplates::getReflection()->getFileName();

		if ($this->getExtends()) {
			$file=$templates->getTemplateFile($extended=$this->getExtends(), $layoutFile);

			$db[Cache::FILES][]=$file; // todo: why?
			$dp[Cache::TAGS][]='dbTemplate#'.$extended->getId();
			return '{extends '.PhpGenerator\Helpers::dump($file).'}'."\n$source";
			}
		if ($layoutFile!==NULL) {
			return '{extends '.PhpGenerator\Helpers::dump($layoutFile).'}{block #content}'."\n$source";
			}
		return $source;
	}
}
