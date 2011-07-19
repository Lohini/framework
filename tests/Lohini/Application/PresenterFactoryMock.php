<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */

namespace App {
	class FooPresenter extends \Lohini\Application\UI\Presenter {}
	class UnimplementedPresenter {}
	abstract class AbstractPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace App\FooModule {
	class BarPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace App\FooModule\BarModule {
	class BazPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace Lohini\Presenters {
	class FooPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace Lohini\Presenters\Foo {
	class BarPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace Lohini\Presenters\Foo\Bar {
	class BazPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace {
	class FooPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace Foo {
	class BarPresenter extends \Lohini\Application\UI\Presenter {}
	}

namespace Foo\Bar {
	class BazPresenter extends \Lohini\Application\UI\Presenter {}
	}
