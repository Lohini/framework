<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Localization;

use Nette\Localization\ITranslator;

interface IEditable
extends ITranslator
{
	public function getVariantsCount();
	public function getStrings($file=NULL);
	public function setTranslation($message, $string, $file);
	public function save($file);
}
