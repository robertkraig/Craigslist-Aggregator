$(document).ready(function()
{
	$('span.near').each(function()
	{
		var href = $(this).prev('a').attr('href');
		href = href.replace('http://','');
		var name = href.split('.')[0];
		$(this).text(name);
	});
	$('.jobsite').hoverWindow({
		'attr':'href',
		'width':'500px'	
	});
});

