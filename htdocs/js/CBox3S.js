function CBox3S(id, data)
{
	var _d=document,
		hovered=false,
		imageAdd='/no-hover-',
		state=data.value,
		b=$('#'+id)
			.hide(),
		s=b.parent(),
		nb=$(_d.createElement('img'))
			.attr('src', data.img_path+imageAdd+data.imgs[state])
			.mouseover(function() {
				hovered=true;
				updateContent(data);
				})
			.mouseout(function() {
				hovered=false;
				updateContent(data);
				})
			.click(function() {
				if (state===1) {
					state= -1;
					}
				else {
					state++;
					}
				updateContent(data);
				})
			.appendTo(s),
		nh=$(_d.createElement('input')).attr({
				'type': 'hidden',
				'name': b.attr('name'),
				'value': state
				})
			.appendTo(s);

	function updateContent(data)
	{
		var imageAdd=hovered? '/hover-' : '/no-hover-';
		nb.attr('src', data.img_path+imageAdd+data.imgs[state]);
		nh.val(state);
	}
}
