// vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
(function($, undefined) {

	$.nette.ext('checkbox3s', {
		load: function() {
			$('body').off('change', 'input[type=checkbox].checkbox3s');
			$('body').on('change', 'input[type=checkbox].checkbox3s', this.changeFn);
			$('input[type=checkbox].checkbox3s')
				.hide()
				.each(this.wrapFn);
			},
		before: function(settings, ui, e) {
			var h, data={};
			$(this.deserializeFn(ui.data)).each(function() {
				if (data[this.name]==undefined) {
					h=$('input[type=hidden][name="'+this.name+'"]');
					data[this.name]= h[0]!=undefined
						? h.val()
						: this.value;
					}
				});
			ui.data=$.param(data);
			}
		}, {
		wrapFn: function() {
			var b=$(this),
				s=b.parent(),
				ext=$.nette.ext('checkbox3s');
			if (s.data('wrapped')) {
				return true;
				}
			s.data('wrapped', true);
			s.addClass('cb3s-cb');
			$('<span class="cb3s cb3s-state"></span>')
				.click(ext.clickFn)
				.on('contextmenu', ext.rClickFn)
				.prependTo(s);
			$('<input></input>')
				.attr({
					type: 'hidden',
					name: b.attr('name'),
					value: b.data('lohini-state')
					})
				.appendTo(s);
			if (b.is(':disabled')) {
				s.addClass('cb3s-disabled');
				}
			ext.updateFn(this);
			},
		changeFn: function(e) {
			if ($(this).is(':disabled')) {
				$(this).children('input[type=checkbox].checkbox3s').addClass('cb3s-disabled');
				}
			else {
				$(this).children('input[type=checkbox].checkbox3s').removeClass('cb3s-disabled');
				}
			},
		clickFn: function() {
			var p=$(this).parent();
			if (p.children('input[type=checkbox]').is(':disabled')) {
				return;
				}
			var dh=p.children('input[type=hidden]'),
				state=parseInt(dh.val());
			if (state==1) {
				state=-1;
				}
			else {
				state++;
				}
			dh.val(state);
			$.nette.ext('checkbox3s').updateFn(this);
			},
		rClickFn: function(e) {
			e.stopImmediatePropagation();
			e.preventDefault();
			var p=$(this).parent();
			if (p.children('input[type=checkbox]').is(':disabled')) {
				return;
				}
			var dh=p.children('input[type=hidden]'),
				state=parseInt(dh.val());
			if (state==-1) {
				state=1;
				}
			else {
				state--;
				}
			dh.val(state);
			$.nette.ext('checkbox3s').updateFn(this);
			},
		updateFn: function(el) {
			var p=$(el).parent(),
				nb=p.children('span');
			switch (parseInt(p.children('input[type=hidden]').val())) {
				case -1:
					nb.removeClass('cb3s-check').removeClass('cb3s-blank').addClass('cb3s-close');
					break;
				case 0:
					nb.removeClass('cb3s-close').removeClass('cb3s-check').addClass('cb3s-blank');
					break;
				case 1:
					nb.removeClass('cb3s-close').removeClass('cb3s-blank').addClass('cb3s-check');
					break;
				}
			},
		deserializeFn: function(data) { // https://github.com/kflorence/jquery-deserialize
			if (data===undefined) {
				return [];
				}
			var parts, ret=[];
			data=data.split('&');
			for (var i=0, length=data.length; i<length; i++) {
				parts=data[i].split('=');
				if (parts[0]!='') {
					ret.push({
						name: decodeURIComponent(parts[0]),
						value: decodeURIComponent(parts[1].replace(/\+/g, '%20'))
						});
					}
				}
			return ret;
			}
		});

	})(jQuery);
