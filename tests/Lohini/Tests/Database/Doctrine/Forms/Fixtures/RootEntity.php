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

use Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RootEntity
extends SharedFieldsEntity
{
	/**
	 * @ORM\Column(type="string")
	 */
	public $name;
	/**
	 * @ORM\ManyToOne(targetEntity="RelatedEntity", cascade={"persist"})
	 * @var RelatedEntity
	 */
	public $daddy;
	/**
	 * @ORM\OneToMany(targetEntity="RelatedEntity", mappedBy="daddy", cascade={"persist"})
	 * @var RelatedEntity[]
	 */
	public $children;
	/**
	 * @ORM\ManyToMany(targetEntity="RelatedEntity", inversedBy="buddies", cascade={"persist"})
	 * @var RelatedEntity[]
	 */
	public $buddies;


	/**
	 * @param string $name
	 */
	public function __construct($name=NULL)
	{
		$this->name=$name;
		$this->children=new ArrayCollection;
		$this->buddies=new ArrayCollection;
	}
}
