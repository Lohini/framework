<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Database\Doctrine\Forms\Fixtures;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RelatedEntity
extends SharedFieldsEntity
{
	/**
	 * @ORM\Column(type="string")
	 */
	public $name;
	/**
	 * @ORM\ManyToOne(targetEntity="RootEntity", inversedBy="children", cascade={"persist"})
	 * @var RootEntity
	 */
	public $daddy;
	/**
	 * @ORM\ManyToMany(targetEntity="RootEntity", mappedBy="buddies", cascade={"persist"})
	 * @var RootEntity[]
	 */
	public $buddies;


	/**
	 * @param string $name
	 * @param RootEntity $daddy
	 */
	public function __construct($name=NULL, RootEntity $daddy=NULL)
	{
		$this->name=$name;
		$this->daddy=$daddy;
		$this->buddies=new \Doctrine\Common\Collections\ArrayCollection;
	}
}
