<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Browser;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class DomException
extends \Exception
{
	/** @var int */
	private $documentLine;
	/** @var string */
	private $source;


	/**
	 * @param int $line
	 */
	public function setDocumentLine($line)
	{
		$this->documentLine=$line;
	}

	/**
	 * @return int
	 */
	public function getDocumentLine()
	{
		return $this->documentLine;
	}

	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source=$source;
	}

	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}
}
