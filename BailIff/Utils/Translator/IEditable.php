<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Utils\Translator;

use Nette\ITranslator;

interface IEditable
extends ITranslator
{
	public function getVariantsCount();
	public function getStrings($file=NULL);
	public function setTranslation($message, $string, $file);
	public function save($file);
}
