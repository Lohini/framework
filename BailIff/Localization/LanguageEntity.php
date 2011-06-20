<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Localization;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Language entity
 *
 * @entity
 * @table(name="langs")
 *
 * @property string $name
 * @property string $nativeName
 * @property string $short
 */
class LanguageEntity
extends \Nella\Doctrine\Entity
{
	/**
	 * @column
	 * @var string
	 */
	private $name;
	/**
	 * @column
	 * @var string
	 */
	private $nativeName;
	/**
	 * @column(length=5)
	 * @var string
	 */
	private $short;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string
	 * @return LanguageEntity
	 */
	public function setName($name)
	{
		$name = trim($name);
		$this->name = $name === "" ? NULL : $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNativeName()
	{
		return $this->nativeName;
	}

	/**
	 * @param string
	 * @return LanguageEntity
	 */
	public function setNativeName($nativeName)
	{
		$nativeName = trim($nativeName);
		$this->nativeName = $nativeName === "" ? NULL : $nativeName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getShort()
	{
		return $this->short;
	}

	/**
	 * @param string
	 * @return LanguageEntity
	 */
	public function setShort($short)
	{
		$short = trim($short);
		$this->short = $short === "" ? NULL : $short;
		return $this;
	}
}
