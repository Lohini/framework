<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\RBAC;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rbac_actions")
 */
class Action
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/**
	 * @ORM\Column(type="string", unique=TRUE)
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $description;


	/**
	 * @param string $name
	 * @param string $description
	 * @throws \Nette\InvalidArgumentException
	 */
	public function __construct($name, $description=NULL)
	{
		if (!is_string($name)) {
			throw new \Nette\InvalidArgumentException('Given name is not string, '.gettype($name).' given.');
			}

		if (substr_count($name, Privilege::DELIMITER)) {
			throw new \Nette\InvalidArgumentException('Given name must not containt '.Privilege::DELIMITER);
			}

		$this->name=$name;
		$this->setDescription($description);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return Action (fluent)
	 */
	public function setDescription($description)
	{
		$this->description=$description;
		return $this;
	}
}
