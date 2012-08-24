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
 * @Lohini\Database\Doctrine\Mapping\DiscriminatorEntry(name="user")
 */
class UserPermission
extends BasePermission
{
	/**
	 * @ORM\ManyToOne(targetEntity="Lohini\Security\Identity")
	 * @ORM\JoinColumn(name="identity_id", referencedColumnName="id")
	 * @var \Lohini\Security\Identity
	 */
	private $identity;


	/**
	 * @param Privilege $privilege
	 * @param \Lohini\Security\Identity $identity
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\InvalidStateException
	 */
	public function __construct(Privilege $privilege, \Nette\Security\IRole $identity)
	{
		if (!$identity instanceof \Lohini\Security\Identity) {
			throw new \Nette\InvalidArgumentException("Given role is not instanceof Lohini\\Security\\Identity, '".get_class($identity)."' given");
			}

		if ($this->identity!==NULL) {
			throw new \Nette\InvalidStateException('Association with identity is immutable.');
			}

		$this->identity=$identity;
		parent::__construct($privilege);
	}

	/**
	 * @return \Lohini\Security\Identity
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * @return \Lohini\Security\Identity
	 */
	public function getRole()
	{
		return $this->identity;
	}

	/**
	 * @return string
	 */
	protected function getRoleId()
	{
		return $this->getRole()->getRoleIds();
	}
}
