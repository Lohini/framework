<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Localization;

/**
 *
 * @author Lopo
 */
interface ITranslator
extends \Nette\Localization\ITranslator
{
	/**
	 * @return array
	 */
	public function getDictionaries();
}
