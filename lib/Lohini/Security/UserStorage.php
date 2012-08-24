<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Security;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class UserStorage
extends \Nette\Http\UserStorage
{
	/** @var \Lohini\Database\Doctrine\Registry */
	private $doctrine;


	/**
	 * @param \Nette\Http\Session $session
	 * @param \Lohini\Database\Doctrine\Registry $doctrine
	 */
	public function __construct(\Nette\Http\Session $session, \Lohini\Database\Doctrine\Registry $doctrine)
	{
		parent::__construct($session);
		$this->doctrine=$doctrine;
	}

	/**
	 * @param bool $need
	 * @return \Nette\Http\SessionSection
	 */
	protected function getSessionSection($need)
	{
		/** @var \stdClass|\Nette\Http\SessionSection $section */
		if ($section=parent::getSessionSection($need)) {
			/** @var SerializableIdentity $identity */
			$identity=$section->identity;
			if ($identity instanceof SerializableIdentity && !$identity->isLoaded()) {
				$identity->load($this->doctrine->getDao('Nette\Security\Identity'));
				}
			}

		return $section;
	}
}
