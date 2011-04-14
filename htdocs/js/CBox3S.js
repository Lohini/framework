// vim: set ts=4 sw=4 ai:
function CBox3S(id, data)
{
	var state=data.value,
		cb='url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAYAAABy6+R8AAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAANJJREFUKM+V0jFOQ0EMhOF/7RnvcofQAy0SEgcKxyF9chMaKq6SJtzhUSThKeQVxNJ2+2lsy+19s5m4sQTwtl7/G2x3uyMC+Pj8WvzUWsNOusXry/OcdK771eoCWIktysl+v79s729FNCxRpSM8vUUUEWQGyqCXKAspyQiqfI2kZHRjJWUxupFEaw2AMe6uUZ3QMcHYJnNuaYyxnFQWlshMMpOIoLXGNE3YWkjyeQb9goiY164FNHpxOHzTe8dlqmqeaYLMBfT0+HDbGW13u5tu7wdOXxehg2jd/gAAAABJRU5ErkJggg==)',
		b=$('#'+id)
			.hide()
			.change(function() {
				if ($(this).is(':disabled')) {
					s.addClass('ui-state-disabled');
					}
				else {
					s.removeClass('ui-state-disabled');
					}
				}),
		s=b
			.parent()
			.css({
				background: cb+' 2px 2px transparent no-repeat',
				border: '0px none'
				})
			.mouseenter(function() {
				if (b.is(':disabled')) {
					return;
					}
				$(this).addClass('ui-state-hover');
				})
			.mouseleave(function() {
				if (b.is(':disabled')) {
					return;
					}
				$(this).removeClass('ui-state-hover');
				}),
		nb=$('<span></span>')
			.addClass('ui-icon')
			.click(function() {
				if (b.is(':disabled')) {
					return;
					}
				if (state===1) {
					state= -1;
					}
				else {
					state++;
					}
				updateCBox3S(data);
				})
			.prependTo(s),
		nh=$('<input></input>')
			.attr({
				type: 'hidden',
				name: b.attr('name'),
				value: data.value
				})
			.appendTo(s);
	if (b.is(':disabled')) {
		s.addClass('ui-state-disabled');
		}
	updateCBox3S(data);

	function updateCBox3S(data)
	{
		nh.val(state);
		switch (state) {
			case -1:
				nb.removeClass('ui-icon-check').addClass('ui-icon-closethick');
				break;
			case 0:
				nb.css('background-position', '-240px -224px'); //blank
				nb.removeClass('ui-icon-closethick');
				break;
			case 1:
				nb.css('background-position', '');
				nb.addClass('ui-icon-check');
				break;
			}
	}
}
