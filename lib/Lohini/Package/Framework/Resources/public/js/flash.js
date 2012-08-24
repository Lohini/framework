/**
 * FLash message fade-out efect
 * extracted from Nette Datagrid
 */
$('div.flash').livequery(function() {
	var el=$(this);
	setTimeout(function() {
		el.animate({opacity: 0}, 2000);
		el.slideUp();
		},
		5000);
});
