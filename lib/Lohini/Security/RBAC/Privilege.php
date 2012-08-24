<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security\RBAC;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rbac_privileges",uniqueConstraints={
 * 	@ORM\UniqueConstraint(name="resource_action_uniq", columns={"resource_id", "action_id"})
 * })
 */
class Privilege
extends \Nette\Object
{
	const DELIMITER='#';

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;
	/**
	 * @ORM\ManyToOne(targetEntity="Resource", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
	 * @var Resource
	 */
	private $resource;
	/**
	 * @ORM\ManyToOne(targetEntity="Action", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="action_id", referencedColumnName="id")
	 * @var Action
	 */
	private $action;


	/**
	 * @param Resource $resource
	 * @param Action $action
	 */
	public function __construct(Resource $resource, Action $action)
	{
		$this->resource=$resource;
		$this->action=$action;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getResource()->getName().self::DELIMITER.$this->getAction()->getName();
	}

	/**
	 * @return Resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @return Action
	 */
	public function getAction()
	{
		return $this->action;
	}
}
