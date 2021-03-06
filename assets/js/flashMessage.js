// vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @author Lopo <lopo@lohini.net>
 * @note based on part of datagrid.js from romansklenar/nette-datagrid
 */
(function($, undefined) {

	/**
	 * Flash message fade-out effect
	 */
	$.nette.ext('flashMessage', {
		load: function() {
			setTimeout(this.timeoutFn, this.timeout, $(this.flashSelector));
			}
		}, {
		flashSelector: 'div.flash',
		timeout: 5000,
		timeoutFn: function(el) {
			el
				.animate({opacity: 0}, 2000)
				.slideUp();
			}
		});

	})(jQuery);
