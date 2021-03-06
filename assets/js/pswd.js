// vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
(function($, undefined) {

	$.nette.ext('pswd', {
		load: function() {
			$('input[type=password].pswdinput').each(this.wrapFn);
			}
		}, {
		wrapFn: function() {
			var p=$(this);
			if (p.data('wrapped')) {
				return true;
				}
			var self=$.nette.ext('pswd');
			p.data('wrapped', true);
			var data=$.extend({}, self.data, p.data('lohini-pswd'));
			if (data.useMasked) {
				p.attr('autocomplete', 'off').hide();
				if (data.rstMasked) {
					p.val('');
					}
				var t=$('<input>')
					.attr({
						type: 'text',
						autocomplete: 'off'
						})
					.insertAfter(p);
				var h=$('<hidden>')
					.attr('name', p.attr('name'))
					.insertAfter(p),
					_w=window,
					m=true,
					tr=null;

				t.focus(function() {
					if (tr===null) {
						if ($.browser.msie) {
							tr=_w.setInterval(function() {
								var t0=t.get(0),
									r=t0.createTextRange(),
									vl=t.val().length,
									ch='character';
								r.moveEnd(ch, vl);
								r.moveStart(ch, vl);
								r.select();
								},
								100);
							}
						else {
							tr=_w.setInterval(function() {
								var vl=t.val().length,
									t0=t.get(0);
								if (!(t0.selectionEnd===vl && t0.selectionStart<=vl)) {
									t0.selectionStart=vl;
									t0.selectionEnd=vl;
									}
								},
								100);
							}
						}
					});

				t.bind('input change', function() {
					m=false;
					self.maskingFn(p, m);
					});
				t.bind('propertychange', function() { self.maskingFn(p, m);});
				t.keyup(function(e) {
					if (!/^(9|1[678]|224|3[789]|40)$/.test(e.keyCode.toString())) {
						m=false;
						self.maskingFn(p, m);
						}
					});
				t.blur(function() {
					_w.clearInterval(tr);
					tr=null;
					m=true;
					self.maskingFn(p, m);
					});
				if (data.rstMasked) {
					$('#'+data.fid).get(0).reset();
					}
				}
			else {
				if (data.useWarning) {
					var wn=$('<strong>')
						.attr({
							title: data.clWarning
							})
						.css({
							background: 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAsklEQVQ4y8WSPQ7CMAyFP1fcKAdghwV1YGXpCuIoCNQDsDLRARYmDtAzPZZUipxUFHXgjbbfTxybJOZg4Qu92QrYAjvXugK3ID3ToqUJerM9cP5iegjSJROIzo+JyddDkiop1n4qSBYkKwjUpQTyZLebYr8q5Su5jiTJfyF1G0jePUXFTKQC7Q+8tiTQlZ4yEr/LBIL0ApoJ7k2czS8xui6BDXB0xBNwD9J79JTnLvE/Ah+vrUwPO4xnIwAAAABJRU5ErkJggg==)',
							display: 'block',
							overflow: 'hidden',
							'background-repeat': 'no-repeat',
							position: 'absolute',
							left: '100%',
							top: '50%',
							margin: '-8px 0 0 -16px',
							width: '16px',
							height: '16px',
							'text-indent': '-100em'
							})
						.hide()
						.insertAfter(p);
					p.keypress(function(e) {
						var cc=e.charCode,
							character;
						if (typeof cc===undefined) {
							cc=e.keyCode;
							}
						character=String.fromCharCode(cc);
						if ((/^[A-Z]$/.test(character) && !e.shiftKey) || (/^[a-z]$/.test(character) && e.shiftKey)) {
							wn.show();
							}
						else if (wn.is(':visible')) {
							wn.hide();
							}
						});
					p.blur(function() {
						if (wn.is(':visible')) {
							wn.hide();
							}
						});
					p.keydown(function(e) {
						if (e.keyCode===20 && wn.is(':visible')) {
							wn.hide();
							}
						});
					}
				if (data.useShow) {
					var t=$('<input>')
						.attr({
							id: 'tf-'+p.attr('id'),
							type: 'text',
							autocomplete: 'off',
							'class': p.attr('class')
							})
						.hide()
						.insertAfter(p);
					var cb=$('<input>')
						.attr({
							type: 'checkbox',
							title: data.cbDesc
							});
					$('<label>')
						.attr({
							'for': 'tf-'+p.attr('id'),
							title: data.cbDesc,
							'class': 'show-password'
							})
						.css({
							display: 'block',
							position: 'static',
							'float': 'none',
							width: 'auto',
							'white-space': 'nowrap',
							'font-size': '0.95em',
							'letter-spacing': '-0.05em'
							})
						.append(
							$('<span>')
								.attr({
									'class': 'ui-icon ui-icon-alert'
									})
								.css({
									display: 'inline-block'
									})
							)
						.append(cb)
						.append(
							$('<span>')
								.css('display', 'inline-block')
								.html(data.cbLabel)
							)
						.appendTo(p.parent());
					p.change(function() { t.val(p.val());});
					t.change(function() { p.val(t.val());});
					cb.click(function() {
						var sf=cb.is(':checked')? t : p;
						var hf=cb.is(':checked')? p : t;
						sf.val(hf.hide().val()).show();
						});
					$(data.fid).submit(function() {
						if (!t.is(':hidden')) {
							p.val(t.val());
							}
						});
					}
				}
			},
		maskingFn: function(p, m) {
			var self=$.nette.ext('pswd'),
				data=$.extend({}, self.data, p.data('lohini-pswd')),
				pp='',
				h=p.parent().children('hidden'),
				t=p.parent().children('input[type=text]');
			if (h.val()!=='') {
				for (var i=0; i<t.val().length; i++) {
					if (t.val().charAt(i)===data.symbol) {
						pp+=h.val().charAt(i);
						}
					else {
						pp+=t.val().charAt(i);
						}
					}
				}
			else {
				pp=t.val();
				}
			var ms=self.encodeFn(pp, m, data.symbol);
			if (h.val()!==pp || t.val()!==ms) {
				h.val(pp);
				p.val(pp);
				t.val(ms);
				}
			},
		encodeFn: function(ps, m, symbol) {
			var cl= m===true? 0 : 1,
				ms='',
				i;
			for (i=0; i<ps.length; i++) {
				if (i<ps.length-cl) {
					ms+=symbol;
					}
				else {
					ms+=ps.charAt(i);
					}
				}
			return ms;
			},
		data: {
			symbol: '\u25cf',
			clWarning: 'Caps-lock is ON!',
			cbLabel: 'Show Password',
			cbDesc: 'Show the password as plain text (not advisable in a public place)',
			rstMasked: true
			}
		});

	})(jQuery);
