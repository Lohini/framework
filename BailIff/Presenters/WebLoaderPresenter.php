<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Presenters;

use Nette\Application\Presenter,
	Nette\Web\IHttpResponse,
	Nette\String,
	Nette\Environment as NEnvironment,
	BailIff\WebLoader\WebLoader;

final class WebLoaderPresenter
extends Presenter
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
		if (($content=WebLoader::getItem(String::webalize($id)))===NULL) {
			$this->terminate(/*IHttpResponse::S404_NOT_FOUND*/); // everything exist, but empty :)
			}
		$sh=$this->getHttpResponse();
		$sh->setHeader('Etag', $content[WebLoader::ETAG]);
		$sh->setExpiration(IHttpResponse::PERMANENT);
//		$sh->setHeader('Cache-Control', 'must-revalidate');
		$inm=NEnvironment::getHttpRequest()->getHeader('if-none-match');
		if ($inm && $inm==$content[WebLoader::ETAG]) {
			$sh->setCode(IHttpResponse::S304_NOT_MODIFIED);
			$this->terminate();
			}
		$sh->setContentType($content[WebLoader::CONTENT_TYPE]);
//		$sh->setHeader('Content-Length', String::length($content[WebLoader::CONTENT]));
		echo $content[WebLoader::CONTENT];
		$this->terminate();
	}
}
