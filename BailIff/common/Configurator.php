<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff;

use Nette\DI\IContainer,
	Nette\DI\Container,
	Nette\Application\UI\Presenter,
	Nette\Application\Routers\Route;

/**
 * BailIff Configurator
 * 
 * @author Lopo <lopo@losys.eu>
 */
class Configurator
extends \Nette\Configurator
{
	/** @var array */
//	public $onCreateContainer=array();


	/**
	 * Gets initial instance of context
	 */
	public function __construct($containerClass='\BailIff\DI\Container')
	{
		parent::__construct($containerClass);

		defined('VAR_DIR') && $this->container->params['varDir']=realpath(VAR_DIR);
		defined('ROOT_DIR') && $this->container->params['rootDir']=realpath(ROOT_DIR);
		defined('BAILIFF_DIR') && $this->container->params['bailiffDir']=realpath(BAILIFF_DIR);

		$this->container->robotLoader;
		//$this->onCreateContainer($this->container);
	}

	/**
	 * @param Container $container
	 * @param array $options
	 * @return \Nette\Application\Application
	 */
	public static function createServiceApplication(Container $container, array $options=NULL)
	{
		$context=new Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', $container->router);
//		$context->addService('console', $container->console);

		Presenter::$invalidLinkMode=
			$container->getParam('productionMode', TRUE)
				? Presenter::INVALID_LINK_SILENT
				: Presenter::INVALID_LINK_WARNING;

		$class= isset($options['class'])? $options['class'] : 'BailIff\Application\Application';
		$application=new $class($context);
		$application->catchExceptions=$container->getParam('productionMode', TRUE);

		$container->params['baseUrl']= $baseUrl= rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$container->params['basePath']=preg_replace('#https?://[^/]+#A', '', $baseUrl);

		return $application;
	}

	/**
	 * @return \Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(Container $container)
	{
		return new \BailIff\Application\PresenterFactory(/*$container->params['appDir'], */$container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return Kdyby\Templates\ITemplateFactory
	 */
	public static function createServiceTemplateFactory(Container $container)
	{
		return new \BailIff\Templating\TemplateFactory($container->latteEngine);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Nette\Latte\Engine
	 */
	public static function createServiceLatteEngine(Container $container)
	{
		$engine=new \Nette\Latte\Engine;
		foreach ($container->getParam('macros', array()) as $macroSet) {
			call_user_func(callback($macroSet), $engine->parser);
			}
//		\BailIff\Latte\Macros\BailIffMacros::install($engine->parser);
		return $engine;
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Nette\Application\Routers\RouteList
	 */
	public static function createServiceRouter(Container $container)
	{
		$router=new \Nette\Application\Routers\RouteList;

		$router[]=new Route('WebLoader/<id>', array(
					'presenter' => 'WebLoader',
					'action' => 'default'
					));
		$router[]=new Route('index.php', array(
					'lang' => $container->getParam('lang', 'en'),
					'module' => 'Core',
					'presenter' => 'Default',
					'action' => 'default',
					), Route::ONE_WAY);

		$router[]=new Route('<lang>/<presenter>/<action>[/<id>]', array(
					'lang' => $container->getParam('lang', 'en'),
					'module' => 'Core',
					'presenter' => 'Default',
					'action' => 'default',
					'id' => NULL
					));

		return $router;
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Diagnostics\Panels\Callback
	 */
	public static function createServiceCallbackPanel(IContainer $container)
	{
		return new \BailIff\Diagnostics\Panels\Callback($container);
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Localization\ITranslator
	 */
	public static function createServiceTranslator(IContainer $container)
	{
		$translator=new \BailIff\Localization\Translator;
		$translator->addFile($container->expand(BAILIFF_DIR.'/lang'), 'BailIff');
		$translator->addFile($container->expand(APP_DIR.'/lang'), 'Application');
		return $translator;
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Localization\Panel
	 */
	public static function createServiceTranslatorPanel(IContainer $container)
	{
		return new \BailIff\Localization\Panel($container);
	}

	/**
	 * Merges 2nd config into 1st
	 *
	 * @param ArrayHash $c1
	 * @param ArrayHash $c2
	 * @return Config
	 */
	public static function mergeConfigs($c1, $c2)
	{
		foreach ($c2 as $k => $v) {
			if (array_key_exists($k, $c1) && $v!==NULL && (!is_scalar($v) || is_array($v))) {
				$c1[$k]=self::mergeConfigs($c1->$k, $c2->$k);
				}
			else {
				$c1[$k]=$v;
				}
			}
		return $c1;
	}
}
