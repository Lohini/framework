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

use BailIff\Localization\Filters;

/**
 * Translation extractor
 */
class Extractor
extends \BailIff\FreezableObject
{
	/** @var \BailIff\Localization\Translator */
	protected $translator;
	/** @var array */
	protected $filters=array();


	/**
	 * @param \BailIff\Localization\Translator
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
	 * @param \BailIff\Localization\IFilter
	 * @return \BailIff\Localization\Extractor
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
