(function($){
	$.fn.hoverWindow = function(settings) {

     var config = {
		 'attr': 'href',
		 'prepend':'',
		 'width':'',
		 'height':'',
		 'backgroundColor':'white'
	 };

     if (settings) $.extend(config, settings);

		var hover_div = document.createElement('div');
		$(hover_div).attr('id','tooltip').prependTo('body');
		var topCord,leftCord;
		var $tooltip = $('#tooltip');
		$tooltip
			.css('position','absolute')
			.css('background-color',config.backgroundColor)
			.css('border','solid 1px black')
			.css('-moz-box-shadow','0px 0px 2px #000')
			.css('-webkit-box-shadow','0px 0px 2px #000')
			.css('padding','5px')
			.css('z-index','100');

		if(config.width.length)
			$tooltip.css('width',config.width);
		if(config.height.length)
			$tooltip.css('height',config.height);

		this.each(function(i){
			var $self = $(this);
			$self.mousemove(function(event){
				topCord = event.clientY+15;
				leftCord = event.clientX+15;
				$tooltip
					.css('top',topCord)
					.css('left',leftCord)
//				console.log('topCord',topCord,'leftCord',leftCord);
			}).mouseover(function(){
				$tooltip.show().html(
					(config.prepend.length?config.prepend:'<strong>' + $self.text() + '</strong>' + '<br />') +
					$self.attr(config.attr)
				);
			}).mouseout(function(){
				$tooltip.hide();
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
	$('.jobsite').hoverWindow({
		'attr':'href',
		'width':'500px',
		'backgroundColor':'rgba(255,255,255,0.8)'
	});
});

