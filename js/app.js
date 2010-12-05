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
			'disabletext':false,
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
					.css('z-index','100')
					.css('opacity','0');
				$tooltip.mouseover(function(){
					$(this).remove();
				});
				return tooltip;
			} else {
				return document.getElementById('tooltip');
			}
		}

		var topCord, leftCord;

		var adjust_location = function($self,event,_topCord,_leftCord)
		{
			_topCord = event.clientY+15;
			if(($(tooltip()).innerHeight()+_topCord) > ($(window).height()-$(tooltip()).innerHeight()))
			{
				_topCord-=$(tooltip()).innerHeight()+25;
			}

			_leftCord = event.clientX+15;
			if(($(tooltip()).innerWidth()+_leftCord) > ($(window).width()))
			{
				_leftCord-=$(tooltip()).innerWidth()+25;
			}

			$(tooltip())
				.css('top',_topCord)
				.css('left',_leftCord)
				.html(
					(
					!config.disabletext
						? (config.prepend.length?config.prepend:'<strong>' + $self.text() + '</strong>' + '<br />') + 
							$self.attr(config.attr)
						: (config.prepend.length?config.prepend:'<strong>' + $self.attr(config.attr) + '</strong>')
					)
				);
		}

		this.each(function(i){

			var $self = $(this);
			$self
				.mousemove(function(event)
				{
					adjust_location($self,event,topCord,leftCord);
				})
				.mouseover(function(event){
					adjust_location($self,event,topCord,leftCord);
					$(tooltip())
						.show()
						.wait(250)
						.animate({opacity: 1}, {duration: 550}, 'linear');
				})
				.mouseout(function(){
					$(tooltip())
						.stop()
						.remove();
				});
		});
	};
})(jQuery);

$(document)
	.data('counter',0);

function centerWindow($div)
{
	var options = { // Default values
		inside:$('#content-container'), // element, center into window
		transition: 0, // millisecond, transition time
		minX:0, // pixel, minimum left element value
		minY:0, // pixel, minimum top element value
		withScrolling:true, // booleen, take care of the scrollbar (scrollTop)
		vertical:true, // booleen, center vertical
		horizontal:true, // booleen, center horizontal
		absolute:true
	};
	var props = {position:'absolute'};
	if (options.vertical)
	{
		var top = (options.inside.height() - $div.outerHeight()) / 2;
		if (options.withScrolling) top += options.inside.scrollTop() || 0;
		top = (top > options.minY ? top : options.minY);
	}
	if (options.horizontal)
	{
		var left = (options.inside.width() - $div.outerWidth()) / 2;
		if (options.withScrolling) left += options.inside.scrollLeft() || 0;
		left = (left > options.minX ? left : options.minX);
	}
	if (options.absolute)
	{
		var absoluteLeft = parseFloat(options.inside.css('left').replace(/[^0-9]+/,''),0);
		var absoluteTop = parseFloat(options.inside.css('top').replace(/[^0-9]+/,''),0);
	}

	props.top = (absoluteTop + top) + 'px';
	props.left = (absoluteLeft + left) + 'px';

	if (options.transition > 0) $div.animate(props, options.transition);
	else $div.css(props);
	return $div;
}

function createDialog(_title,_url)
{
	var incrementor = ($(document).data().counter++)+1;
	var div = document.createElement('div');
	$(div).hide();
	$(div).addClass('window');
	$(div).attr('id','window_'+incrementor);
	$('#link_content').prepend(div);
	var iframe = document.createElement('iframe');
	$(iframe).attr('border','0');
	$(iframe).attr('id','iframe_'+incrementor);
	$(iframe).attr('src',_url);
	$(iframe).attr('title',_title);
	$(div).append(iframe);
	$('#open_windows').prepend('<a class="windowLink button" info="'+_title+'" rel="'+incrementor+'" id="link_'+incrementor+'">Window&nbsp;'+ incrementor +'&nbsp;<span>X</span></a>');
	$('#link_'+incrementor).hoverWindow({
		'attr':'info',
		'disabletext':true,
		'width':'500px'
	});
}

function process_data(json)
{
	var output = '';
	var date;
	var location;
	var info,link,not_near;
	for(var i in json)
	{
		date = json[i].date;
		output += '<h1>' + date + '</h1>';
		output += '<div class="date">';
		var tmp_location = '';
		for(var j in json[i].records)
		{
			location = json[i].records[j].location.split('.')[0];
			if(tmp_location != location)
			{
				if(tmp_location != '') // first iteration
					output+='</ul>';

				output+='<h2>' + location + '</h2>';
				output+='<ul>';
			}
			tmp_location = location;
			info = json[i].records[j].info;
			not_near = info.url.replace('http://', '').split('.')[0] != location?'<span class="near"></span>':'';
			link = info.url.match(/http:\/\//)?info.url:'http://'+info.from+info.url;
			output+='<li><a href="' + link + '" class="jobsite" info="'+info.title+'" target="_blank"><span>' + info.title + ' : <span style="color:black;">' + info.field + '</span></span></a>' + not_near + '</li>';
		}
		output+='</div>';
	}
	return output;
}

function content_size()
{
	$('#content-container').css('left',$('#find_items').innerWidth(true));
	$('#content,#link_content')
		.css('height',$(window).height()-60)
		.css('width',$(window).width() - ($('#find_items').innerWidth(true)+25))
		.css('margin-left','10px');
}

$('#content h1')
	.live('click',function()
	{
		$(this).next('div.date').toggle();
	});

$('#content h2')
	.live('click',function()
	{
		$(this).next('ul').toggle();
	});

function hoverReset(event)
{
	event.preventDefault();
	$('#buttons a.button, #open_windows a.button').removeClass('hover');
	$(this).addClass('hover');
}

$('.windowLink')
	.live('click',function(event){
		event.preventDefault();
		hoverReset(event);
		$(this).addClass('hover');
		$('#open_windows').show();
		$('#link_content').show();
		$('#content').hide();
		$('.window').hide();
		var id = $(this).attr('rel');
		var $iframe = $('#iframe_'+id);
		var _w = parseInt($('#link_content').css('width').replace('px', '')) - 50 + 'px';
		var _h = parseInt($('#link_content').css('height').replace('px', '')) - 50 + 'px';
		$iframe.css('width',_w);
		$iframe.css('height',_h);
		$('#window_'+id).show();
		$('#toggle_disp').hide();
		$('#show_search').show();
		$('title').text($(document).data().title+' : '+$(this).attr('info'));
	});

$('a.jobsite')
	.live('click',function(event)
	{
		event.preventDefault();
		createDialog($(this).text(),$(this).attr('href'));
	});

$('#open_windows a.windowLink span')
	.live('click',function(event)
	{
		event.preventDefault();
		var $parent = $(this).parent();
		var id = $parent.attr('rel');
		$('#window_'+id).remove();
		$parent.remove();
	});

$(function(){
	$(document)
		.data('title',$('title').text());

	$('#buttons a.button')
		.click(hoverReset);

	$('#show_search')
		.live('click',function(event)
		{
			event.preventDefault();
			$('#link_content').hide();
			$('#content').show();
			$('#toggle_disp').show();
			$(this).hide();
			$('title').text($(document).data().title);
		});

	$('#toggle_disp')
		.data('open',true)
		.click(function(event)
		{
			event.preventDefault();
			if($(this).data().open)
			{
				$(this).text('Expand All');
				$('#content div.date').hide();
				$('#content div.date ul').css('display','none');
				$(this).data().open = false;
			}
			else
			{
				$('#content div.date').show();
				$('#content div.date ul').css('display','block');
				$(this).data().open = true;
				$(this).text('Close All');
			}
			$('#show_search').hide();
		});

	$('#search_btn')
		.click(function(event){
			event.preventDefault();
			$('#find_items').submit();
		});

	$('input[type="checkbox"].regions')
		.click(function()
		{
			var region = $(this).val();
			var str = 'input[name="include[]"].'+region;
			var $regions = $(str);
			if($(this).is(':checked'))
			{
				$regions.attr('checked','checked');
			}
			else
			{
				$regions.removeAttr('checked');
			}
		});

	$('#find_items')
		.submit(function(){
			if(!$('input[name="include[]"]:checked').length)
			{
				$('input[value="socal"]').attr('checked','checked');
				$('input[name="include[]"].socal').attr('checked','checked');
			}
			if($('#search_term').val() == "")
			{
				$('#search_term')
					.css('-moz-box-shadow','0px 0px 2px red')
					.css('-webkit-box-shadow','0px 0px 2px red')
					.css('border','solid 1px red')
				return false;
			}
			$('#loader').show();
			$('#link_content').hide();
			$('#content').show().html('Loading...');
			$('#search_btn').val('searching');
			$('#buttons').hide();
			$.ajax({
				type: "POST",
				url: window.PHP_SELF,
				data: $('#find_items').serialize(),
				dataType: 'json',
				success: function(json){
					$('#content').html(process_data(json));
					$('#buttons').show();
					$('#search_btn').val('Search Craigslist');
					$('#loader').hide();
					$.getScript('/js/nav.js');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					try{
						console.log(XMLHttpRequest, textStatus, errorThrown);					
					}
					catch(e){}
				}
			});
			return false;
		});

	content_size();

	$(window)
		.resize(content_size);

	$('#donate')
		.click(function(event){
			event.preventDefault();
			var form = ' \
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post"> \
				<input name="cmd" value="_xclick" type="hidden" /> \
				<input name="business" value="robertkraig@gmail.com" type="hidden" /> \
				<input name="item_name" value="Donation to the Robert Kraig Fund." type="hidden" /> \
				<input name="item_number" value="" type="hidden" /> \
				<input name="no_shipping" value="0" type="hidden" /> \
				<input name="no_note" value="1" type="hidden" /> \
				<input name="currency_code" value="USD" type="hidden" /> \
				<input name="bn" value="PP-BuyNowBF" type="hidden" /> \
				<div style="font-size: 24px;line-height:30px; padding:10px; text-align:center;">Donate to author</div> \
				<div style="padding:10px; padding-top:0px; margin:0px; text-align:center;"> \
					<label style="display:inline;" for="amount">$<input name="amount" value="" type="text" /></label> \
					<button style="display:inline;" name="submit" type="submit">Donate</button>\
				</div> \
			</form>';

			var $modal =
				$('<div/>')
				.attr('id','mask')
				.css('background-color','rgba(0,0,0,.65)')
				.css('position','absolute')
				.css('top','0').css('left','0')
				.css('z-index','1020')

			var $load = 
				$('<div/>')
				.attr('id','load')
				.css('width','275px')
				.css('height','100px')
				.css('position','absolute')
				.css('top','0').css('left','0')
				.css('z-index','1050')
				.css('background-color','white')
				.css('border','solid 1px black')
				.css('-moz-box-shadow','0px 0px 2px #000')
				.css('-webkit-box-shadow','0px 0px 2px #000')
				.append(form);

			$('body')
				.prepend($modal)
				.prepend($load);

			$modal
				.click(function(){
					$modal.remove();
					$load.remove();
				});

			//Get the screen height and width
			var maskHeight = $(document).height();
			var maskWidth = $(window).width();

			//Set heigth and width to mask to fill up the whole screen
			$('#mask').css({'width':maskWidth,'height':maskHeight});
			centerWindow($load);
		});
});