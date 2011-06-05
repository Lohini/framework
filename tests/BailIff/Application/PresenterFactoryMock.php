<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

namespace App {
	class FooPresenter extends \BailIff\Application\UI\Presenter {}
	class UnimplementedPresenter {}
	abstract class AbstractPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace App\FooModule {
	class BarPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace App\FooModule\BarModule {
	class BazPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace BailIff\Presenters {
	class FooPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace BailIff\Presenters\Foo {
	class BarPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace BailIff\Presenters\Foo\Bar {
	class BazPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace {
	class FooPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace Foo {
	class BarPresenter extends \BailIff\Application\UI\Presenter {}
	}

namespace Foo\Bar {
	class BazPresenter extends \BailIff\Application\UI\Presenter {}
	}
