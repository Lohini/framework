<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Localization\Filters;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votocek
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Localization\Dictionary;

/**
 * PHP file extractor filter (Nella methods / functions predefined)
 */
class Nella
extends \Nette\Object
implements \Lohini\Localization\IFilter
{
	public $exts=array("*.php");

	/** @var array functions map key is function name value is translation position */
	public $functions=array(
		// Basic
		'__' => 1,
		'translate' => 1,
		// Forms
		'addText' => 2,
		'addPassword' => 2,
		'addTextArea' => 2,
		'addFile' => 2,
		'addHidden' => 2,
		'addCheckbox' => 2,
		'addRadioList' => 2,
		'addSelect' => 2,
		'addMultiselect' => 2,
		'addSubmit' => 2,
		'addButton' => 2,
		'addImage' => 3,
		'addEmail' => 2,
		'addUrl' => 2,
		'addNumber' => 2,
		'addRange' => 2,
		'addDate' => 2,
		'addDateTime' => 2,
		'addTime' => 2,
		'addSearch' => 2,
		'addEditor' => 2,
		'addMultipleFile' => 2,
		'addEmail' => 2,
		'addProtection' => 1,
		'addGroup' => 1,
		'addRule' => 2,
		'setRequired' => 1,
		'setOption' => 2,
	);

	/**
	 * @param \Lohini\Localization\Dictionary
	 */
	public function process(Dictionary $dictionary)
	{
		$dictionary->freeze();

		$files=\Nette\Utils\Finder::findFiles($this->exts)->from($dictionary->dir);
		foreach ($files as $file) {
			$translations=$this->parse(file_get_contents($file->getRealpath()));
			foreach ($translations as $message) {
				$translation=(array) $message;
				$message= is_array($message)? reset($message) : $message;

				if ($dictionary->hasTranslation($message)) {
					continue;
					}

				$dictionary->addTranslation($message, $translation, Dictionary::STATUS_UNTRANSLATED);
				}
			}
	}

	/**
	 * @param string
	 * @return array
	 */
	final protected function parse($content)
	{
		$data=array();

		$next=false;
		$array=false;
		$i=0;
        foreach (token_get_all($content) as $token) {
			if (is_array($token)) {
                if ($token[0]!=T_STRING && $token[0]!=T_CONSTANT_ENCAPSED_STRING && $token[0]!=T_ARRAY) {
					continue;
					}

                if ($token[0]==T_STRING && isset($this->functions[$token[1]])) {
                    $next=$this->functions[$token[1]];
                    continue;
	                }

				if ($token[0]==T_ARRAY && $next==1 && !$array) {
					$array=TRUE;
					continue;
					}

				if ($token[0]==T_CONSTANT_ENCAPSED_STRING && $next==1 && $array) {
					if (!isset($data[$i]) || !is_array($data[$i])) {
						$data[$i]=array();
						}
					$s=substr($token[1], 1, -1);
					if ($s) {
						$data[$i][]=$s;
						}
					continue;
					}

                if ($token[0]==T_CONSTANT_ENCAPSED_STRING && $next==1 && !$array) {
					$s=substr($token[1], 1, -1);
					if ($s) {
						$data[$i]=$s;
						}
                    $next=FALSE;
					$i++;
					}
				}
			else {
                if ($token==')' && !$array) {
					$next=FALSE;
					}
				elseif ($token==')' && $array) {
					$array=$next=FALSE;
					$i++;
					}

                if ($token==',' && $next!=FALSE && !$array) {
					$next-=1;
					}
				}
			}

		return $data;
	}
}
