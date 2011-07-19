/*
 * @author Filip Procházka
 * @license GPL
 * @author Lopo <lopo@lohini.net> (Lohini port)
 */
$(function() {
	// vhodně nastylovaný div vložím po načtení stránky
	$('<div id="ajax-spinner"><div class="image"></div></div>')
		.hide()
		.appendTo('body')
		.ajaxStop(function() {
			// a při události ajaxStop spinner schovám a nastavím mu původní
			// pozici
			$(this).hide().css({
				position: 'fixed'
				});
			})/*
		.ajaxStart(function() {
			$(this).show();
			})*/;

	// zajaxovatění odkazů
	$('a.ajax')
		.live('click', function(event) {
			event.preventDefault();
			$.get(this.href, $.nette.success);
			// zobrazení spinneru a nastavení jeho pozice
			$('#ajax-spinner').show();
			});

	// ajaxové formuláře
	// odeslání na formulářích
	$('body')
		.delegate('form.ajax', 'submit', function(e) {
			if (!Nette.validateForm(this)) {
				return false;
				}
			// tiny MCE HACK
			if (typeof tinyMCE != 'undefined') {
				for (var editorID in tinyMCE.editors) {
					if (editorID!=null && editorID!=0) {
						tinyMCE.get(editorID).save();
						}
					}
				}
			$('#ajax-spinner').show();
			// If exist file
			if ($(this).find('input[type=file]').length===0) {
				$(this).ajaxSubmit(e, {
					success : $.nette.success
					});
				}
			else {
				$(this).submit(e);
				}
			})
		.delegate('form.ajax :submit', 'click', function(e) {
			e= e || event;
			var target= e.target || e.srcElement;
			this['nette-submittedBy']= (target.type in {
				submit : 1,
				image : 1
				}) ? target : null;
			if (typeof tinyMCE != 'undefined') {
				for (var editorID in tinyMCE.editors) {
					if (editorID!=null && editorID!=0) {
						tinyMCE.get(editorID).save();
						}
					}
				}
			if (!Nette.validateForm(this.form)) {
				return false;
				}
			$(this).ajaxSubmit(e, {
				success : $.nette.success
				});
			});
	});
