(function($){
	$.fn.wait = function(time, type) {
		time = time || 1000;
		type = type || "fx";
		return this.queue(type, function() {
			var self = this;
			setTimeout(function() {
				$(self).dequeue();
			}, time);
		});
	};

	$.fn.hoverWindow = function(settings) {

		var config = {
			'attr': 'href',
			'prepend':'',
			'width':'',
			'height':'',
			'backgroundColor':'white'
		};

		if (settings) $.extend(config, settings);

		var tooltip = function ()
		{
			if(!$('#tooltip').length)
			{
				var tooltip = document.createElement('div');
				$(tooltip).attr('id','tooltip').hide().prependTo('body')
				var $tooltip = $('#tooltip');

				if(config.width.length)
					$tooltip.css('width',config.width);
				if(config.height.length)
					$tooltip.css('height',config.height);

				$tooltip
					.css('position','absolute')
					.css('background-color',config.backgroundColor)
					.css('border','solid 1px black')
					.css('-moz-box-shadow','0px 0px 2px #000')
					.css('-webkit-box-shadow','0px 0px 2px #000')
					.css('padding','5px')
					.css('opacity','0')
					.css('z-index','100');
				$tooltip.mouseover(function(){
					$(this).remove();
				});
				return tooltip;
			} else {
				return document.getElementById('tooltip');
			}
		}

		var topCord, leftCord;

		this.each(function(i){

			var $self = $(this);
			$self.mousemove(function(event)
			{
				topCord = event.clientY+15;
				if(($(tooltip()).innerHeight()+topCord) > ($(window).height()-$(tooltip()).innerHeight()))
				{
					topCord-=$(tooltip()).innerHeight()+25;
				}

				leftCord = event.clientX+15;
				if(($(tooltip()).innerWidth()+leftCord) > ($(window).width()))
				{
					leftCord-=$(tooltip()).innerWidth()+25;
				}

				$(tooltip())
					.css('top',topCord)
					.css('left',leftCord)
					.html(
						(config.prepend.length?config.prepend:'<strong>' + $self.text() + '</strong>' + '<br />') +
						$self.attr(config.attr)
					);
			})
			.mouseover(function(){ $(tooltip()).show().wait(250).animate({opacity: 1}, {duration: 250}, 'linear');})
			.mouseout(function(){ $(tooltip()).animate({opacity: 0}, {duration: 100, queue: false}, 'linear',function(){ $(tooltip()).remove(); }); });
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
		'backgroundColor':'rgba(255,255,255,0.9)'
	});
});

