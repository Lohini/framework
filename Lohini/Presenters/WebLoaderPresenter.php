<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Presenters;

use Nette\Http\IResponse,
	Lohini\WebLoader\WebLoader;

final class WebLoaderPresenter
extends \Nette\Application\UI\Presenter
{
	/**
	 * Sends compiled css/js
	 * @param string $id
	 */
	public function renderDefault($id=NULL)
	{
		$this->setLayout(FALSE);
		if ($id===NULL) {
			$this->terminate();
			}
		if (($content=WebLoader::getItem(\Nette\Utils\Strings::webalize($id)))===NULL) {
			$this->terminate(/*IResponse::S404_NOT_FOUND*/); // everything exist, but empty :)
			}
		$sh=$this->getHttpResponse();
		$sh->setHeader('Etag', $content[WebLoader::ETAG]);
		$sh->setExpiration(IResponse::PERMANENT);
//		$sh->setHeader('Cache-Control', 'must-revalidate');
		$inm=$this->getHttpRequest()->getHeader('if-none-match');
		if ($inm && $inm==$content[WebLoader::ETAG]) {
			$sh->setCode(IResponse::S304_NOT_MODIFIED);
			$this->terminate();
			}
		$sh->setContentType($content[WebLoader::CONTENT_TYPE]);
//		$sh->setHeader('Content-Length', Strings::length($content[WebLoader::CONTENT]));
		echo $content[WebLoader::CONTENT];
		$this->terminate();
	}
}
