<?php //vim: set ts=4 sw=4 ai:
namespace BailIff\Components;

use Nette\Application\UI\Control,
	Nette\Utils\Html;

/**
 * @author Lopo <lopo@losys.eu>
 */
class Gravatar
extends Control
{
	/** @var int Size in pixels */
	public $size=32;
	/** @var string Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ] */
	public $default='mm';
	/** @var string Maximum rating (inclusive) [ g | pg | r | x ] */
	public $rating=NULL;
	/** @var array Optional, additional key/value attributes to include in the IMG tag */
	public $atts=array();

	/**
	 * renders link
	 */
	public function render()
	{
		$email=func_get_arg(0);
		$url='http://www.gravatar.com/avatar/'
			.md5(strtolower(trim($email)))
			.'?d='.$this->default
			.'&s='.$this->size
			.($this->rating!==NULL? '&r='.$this->rating : '');
		$img=Html::el('img')
			->src($url)
			->alt('')
			->width($this->size)
			->height($this->size);
		foreach ($this->atts as $k => $v) {
			$img->$k=$v;
			}
		echo $img;
	}
}
