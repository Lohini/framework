<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Localization;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Localization\Filters;

/**
 * Translation extractor
 */
class Extractor
extends \Lohini\FreezableObject
{
	/** @var \Lohini\Localization\Translator */
	protected $translator;
	/** @var array */
	protected $filters=array();


	/**
	 * @param \Lohini\Localization\Translator
	 */
	public function __construct(Translator $translator)
	{
		$this->translator=$translator;
		$this->addFilter(new Filters\Latte);

		$this->onFreeze[]=function(Extractor $extractor) { // Setup default filters
			$extractor->addFilter(new Filters\Latte);
			$extractor->addFilter(new Filters\Nella);
			};
	}

	/**
	 * @param \Lohini\Localization\IFilter
	 * @return \Lohini\Localization\Extractor
	 * @throws \Nette\InvalidStateException
	 */
	public function addFilter(IFilter $filter)
	{
		$this->updating();
		$this->filters[]=$filter;
		return $this;
	}

	/**
	 * @internal
	 */
	public function run()
	{
		$this->updating();
		$this->freeze();

		foreach ($this->translator->dictionaries as $dictionary) {
			if (!$dictionary->frozen) {
				$dictionary->init($this->translator->lang);
				}
			foreach ($this->filters as $filter) {
				$filter->process($dictionary);
				}
			}
	}
}
