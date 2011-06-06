<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components;

/**
 * Gravatar component
 * 
 * @link http://en.gravatar.com
 * @author Lopo <lopo@losys.eu>
 */
class Gravatar
extends \Nette\Application\UI\Control
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
	 * @param string $email
	 * @return \Nette\Utils\Html
	 */
	protected function getElement($email)
	{
		$url='http://www.gravatar.com/avatar/'
			.md5(strtolower(trim($email)))
			.'?d='.$this->default
			.'&s='.$this->size
			.($this->rating!==NULL? '&r='.$this->rating : '');
		$img=\Nette\Utils\Html::el('img')
			->src($url)
			->alt('')
			->width($this->size)
			->height($this->size);
		foreach ($this->atts as $k => $v) {
			$img->$k=$v;
			}
		return $img;
	}

	/**
	 * renders link
	 * @param string
	 */
	public function render()
	{
		echo $this->getElement(func_get_arg(0))->__toString();
	}

	/**
	 * @param string $email
	 * @param int $size
	 * @param string $default
	 * @param string $rating
	 * @return \Nette\Utils\Html
	 */
	public static function helper($email, $size=32, $default='mm', $rating=NULL)
	{
		$gi=new self;
		$gi->size=$size;
		$gi->default=$default;
		$gi->rating=$rating;
		return $gi->getElement($email);
	}
}
