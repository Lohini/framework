// vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
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
			s.css({
				background: ext.cb+' 2px 2px transparent no-repeat',
				border: '0px none'
				})
				.mouseenter(function() {
					if ($(this).is(':disabled')) {
						return;
						}
					$(this).addClass('ui-state-hover');
					})
				.mouseleave(function() {
					if ($(this).is(':disabled')) {
						return;
						}
					$(this).removeClass('ui-state-hover');
					});
			$('<span class="ui-icon"></span>')
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
				s.addClass('ui-state-disabled');
				}
			ext.updateFn(this);
			},
		changeFn: function(e) {
			if ($(this).is(':disabled')) {
				$(this).children('input[type=checkbox].checkbox3s').addClass('ui-state-disabled');
				}
			else {
				$(this).children('input[type=checkbox].checkbox3s').removeClass('ui-state-disabled');
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
					nb.css('background-position', '');
					nb.removeClass('ui-icon-check').addClass('ui-icon-closethick');
					break;
				case 0:
					nb.css('background-position', '-240px -224px'); //blank
					nb.removeClass('ui-icon-closethick').removeClass('ui-icon-check');
					break;
				case 1:
					nb.css('background-position', '');
					nb.removeClass('ui-icon-closethick').addClass('ui-icon-check');
					break;
				}
			},
		deserializeFn: function(data) { // https://github.com/kflorence/jquery-deserialize
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
			},
		cb: 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAYAAABy6+R8AAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAANJJREFUKM+V0jFOQ0EMhOF/7RnvcofQAy0SEgcKxyF9chMaKq6SJtzhUSThKeQVxNJ2+2lsy+19s5m4sQTwtl7/G2x3uyMC+Pj8WvzUWsNOusXry/OcdK771eoCWIktysl+v79s729FNCxRpSM8vUUUEWQGyqCXKAspyQiqfI2kZHRjJWUxupFEaw2AMe6uUZ3QMcHYJnNuaYyxnFQWlshMMpOIoLXGNE3YWkjyeQb9goiY164FNHpxOHzTe8dlqmqeaYLMBfT0+HDbGW13u5tu7wdOXxehg2jd/gAAAABJRU5ErkJggg==)'
		});

	})(jQuery);
