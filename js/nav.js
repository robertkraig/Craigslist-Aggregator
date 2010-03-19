(function($){
	$.fn.hoverWindow = function(){
		this.each(function(count){
			var hover_div = document.createElement('div');
			var $hover = $(hover_div);
			$(this).parent().css('position','relative');
			$hover
				.attr('id','hover_'+count)
				.addClass('linkhover')
				.css('position','absolute')
				.css('z-index','1')
				.css('padding','2px')
				.css('left',$(this).outerWidth(true)+5)
				.css('top','-2')
				.css('display','none')
				.html($(this).attr('href'));
			$(this).attr('id','link_'+count);
			$(this).parent().prepend($hover);
			var id = $(this).attr('id').split('_')[1];
			$(this).parent().hover(function(){
				$('#hover_'+id).show();
			},function(){
				$('#hover_'+id).hide();
			});
		});
	};
})(jQuery);

$(document).ready(function(){
	$('#toggle_disp a').live('click',function(){
		if($(this).attr('rel') == 'open')
		{
			$(this).text('Expand All');
			$('#content div.date').hide();
			$('#content div.date ul').css('display','none');
			$(this).attr('rel', 'close');
		}else{
			$('#content div.date').show();
			$('#content div.date ul').css('display','block');
			$(this).attr('rel', 'open');
			$(this).text('Close All');
		}
		return false;
	});
	$('span.near').each(function(){
		var href = $(this).prev('a').attr('href');
		href = href.replace('http://','');
		var name = href.split('.')[0];
		$(this).text(name);
	});
	$('.jobsite').hoverWindow();
});

