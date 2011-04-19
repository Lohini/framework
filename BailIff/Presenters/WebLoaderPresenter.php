<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Presenters;

use Nette\Application\UI\Presenter,
	Nette\Http\IResponse,
	Nette\StringUtils,
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
		if (($content=WebLoader::getItem(StringUtils::webalize($id)))===NULL) {
			$this->terminate(/*IResponse::S404_NOT_FOUND*/); // everything exist, but empty :)
			}
		$sh=$this->getHttpResponse();
		$sh->setHeader('Etag', $content[WebLoader::ETAG]);
		$sh->setExpiration(IResponse::PERMANENT);
//		$sh->setHeader('Cache-Control', 'must-revalidate');
		$inm=NEnvironment::getHttpRequest()->getHeader('if-none-match');
		if ($inm && $inm==$content[WebLoader::ETAG]) {
			$sh->setCode(IResponse::S304_NOT_MODIFIED);
			$this->terminate();
			}
		$sh->setContentType($content[WebLoader::CONTENT_TYPE]);
//		$sh->setHeader('Content-Length', StringUtils::length($content[WebLoader::CONTENT]));
		echo $content[WebLoader::CONTENT];
		$this->terminate();
	}
}
