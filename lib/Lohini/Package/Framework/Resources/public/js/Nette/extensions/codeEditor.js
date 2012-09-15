/**
 * @author Filip Procházka <filip@prochazka.su>
 */
(function($, undefined) {

	/**
	 */
	$.nette.ext('codeEditor', {
		before: function (settings, ui) {
			if (ui) {
				var $editors = $(ui).closest('form').find('textarea.code-editor');
				$editors.each(function () {
					var $editor = $(this);
					var codeEditor = $editor.data('code-editor');
					if (codeEditor) {
						$editor.val(codeEditor.getValue());
						settings.data[$editor.attr('name')] = $editor.val();
					}
				});
			}
		}
	});

})(jQuery);
