// vim: set ts=4 sw=4 ai:
var _d=document,
	_w=window;
function PswdInput(id, d)
{
	var p=$('#'+id),
		frm=$('#'+d.fid),
		wn, t, h, m, tr, cb, sf, hf;

	function encod(ps)
	{
		var ms='',
			i;
		for (i=0; i<ps.length; i++) {
			if (i<ps.length-(m===true? 0 : 1)) {
				ms+=d.masked.symbol;
				}
			else {
				ms+=ps.charAt(i);
				}
			}
		return ms;
	}

	function doMasking()
	{
		var pp='',
			i, ms;
		if (h.val()!=='') {
			for (i=0; i<t.val().length; i++) {
				if (t.val().charAt(i)===d.masked.symbol) {
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
		ms=encod(pp);
		if (h.val()!==pp || t.val()!==ms) {
			h.val(pp);
			p.val(pp);
			t.val(ms);
			}
	}

	if (d.clwarning && !d.masked) {
		wn=$(_d.createElement('strong'))
			.attr({
				'class': 'capslock-warning',
				'title': d.clwarning.str
				})
			.html(d.clwarning.str)
			.hide()
			.insertAfter(p);
		p.keypress(function(e) {
			var cc=e.charCode,
				character;
			if (typeof cc==='undefined') {
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
	if (d.showpswd && !d.masked) {
		t=$(_d.createElement('input'))
			.attr({
				'id': 'tf-'+id,
				'type': 'text',
				'autocomplete': 'off',
				'class': p.attr('class')
				})
			.hide()
			.insertAfter(p);
		cb=$(_d.createElement('input'))
			.attr({
				'type': 'checkbox',
				'title': d.showpswd.cb.desc
				});
		$(_d.createElement('label'))
			.attr({
				'for': 'tf-'+id,
				'title': d.showpswd.cb.desc,
				'class': 'show-password'
				})
			.css({
				'display': 'block',
				'position': 'static',
				'float': 'none',
				'width': 'auto'
				})
			.append(cb)
			.append(
				$(_d.createElement('span'))
					.css('display', 'inline-block')
					.html(d.showpswd.cb.label)
				)
			.appendTo(p.parent());
		p.change(function() { t.val(p.val());});
		t.change(function() { p.val(t.val());});
		cb.click(function() {
			sf=cb.is(':checked')? t : p;
			hf=cb.is(':checked')? p : t;
			sf.val(hf.hide().val()).show();
			});
		frm.submit(function() {
			if (!t.is(':hidden')) {
				p.val(t.val());
				}
			});
		}
	if (d.masked) {
		p.attr('autocomplete', 'off').hide();
		if (d.masked.reset) {
			p.val('');
			}
		t=$(_d.createElement('input'))
			.attr({
				'type': 'text',
				'autocomplete': 'off'
				})
			.insertAfter(p);
		h=$(_d.createElement('hidden'))
			.attr('name', p.attr('name'))
			.insertAfter(p);
		m=true;
		tr=null;

		t.focus(function() {
			if (tr===null) {
				if (typeof _d.uniqueID!=='undefined') {
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
			doMasking();
			});
		t.bind('propertychange', function() { doMasking();});
		t.keyup(function(e) {
			if (!/^(9|1[678]|224|3[789]|40)$/.test(e.keyCode.toString())) {
				m=false;
				doMasking();
				}
			});
		t.blur(function() {
			_w.clearInterval(tr);
			tr=null;
			m=true;
			doMasking();
			});
		if (d.masked.reset) {
			frm.get(0).reset();
			}
		} // d.masked
}
