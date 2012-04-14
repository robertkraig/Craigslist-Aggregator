(function ($) {

	$.fn.wait = function(time, type)
	{
		time = time || 1000;
		type = type || "fx";
		return this.queue(type, function() {
			var self = this;
			setTimeout(function() {
				$(self).dequeue();
			}, time);
		});
	}

	$.fn.hoverWindow = function(settings)
	{
		var config = {
			'attr':'href',
			'disabletext':false,
			'prepend':'',
			'width':'',
			'height':'',
			'backgroundColor' : 'white'
		};

		if(settings)
		{
			$.extend(config, settings);
		}

		function tooltip()
		{
			if(!$('#tooltip').length)
			{
				var $tooltip = $('<div/>')
					.attr('id','tooltip')
					.hide()
					.prependTo('body');

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
					.css('opacity','0')
					.mouseover(function()
					{
						$(this).remove();
					});
				return $tooltip;
			} else {
				return $('#tooltip');
			}
		}

		var topCord, leftCord;

		var adjust_location = function($self,event,_topCord,_leftCord)
		{
			_topCord = event.clientY+15;
			if((tooltip().innerHeight()+_topCord) > ($(window).height()-tooltip().innerHeight()))
			{
				_topCord-=tooltip().innerHeight()+25;
			}

			_leftCord = event.clientX+15;
			if((tooltip().innerWidth()+_leftCord) > ($(window).width()))
			{
				_leftCord-=tooltip().innerWidth()+25;
			}

			tooltip()
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
					tooltip()
						.show()
						.wait(250)
						.animate({
							opacity: 1
						}, {
							duration: 550
						}, 'linear');
				})
				.mouseout(function(){
					tooltip()
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
	var props = {
		position:'absolute'
	};
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
	var $window = $('<div>',{
		'class':'window',
		'id':'window_'+incrementor,
		'style':'display:none;'
	}).prependTo('#link_content');

	$('<iframe>',{
		'border':'0',
		'id':'iframe_'+incrementor,
		'src':_url,
		'title':_title
	}).appendTo($window);

	$('<a>',{
		'class':'windowLink button',
		'info':_title,
		'rel':incrementor,
		'id':'link_'+incrementor
	})
	.html('Window&nbsp;'+ incrementor +'&nbsp;<span>X</span>')
	.prependTo('#open_windows')
	.hoverWindow({
		'attr':'info',
		'disabletext':true,
		'width':'500px'
	});
}

function process_data(json)
{

	var date,location,info,link,not_near;
	var $container = $('#content');
	$container.empty();

	$.each(json,function(key, dateGroup)
	{
		var date = dateGroup.date;

		$('<h1>').text(date).appendTo($container);

		var $dateGroup = $('<div>',{
			'class':'date'
		}).appendTo($container);

		var tmp_location_switch = '';

		$.each(dateGroup.records, function(key, locationGroup)
		{
			var location = locationGroup.location.split('.')[0];
			if(tmp_location_switch != location)
			{
				if(!$('div[group='+location+']',$dateGroup).length)
				{
					var $group = $('<div>',{
						group:location
					}).appendTo($dateGroup);
					$('<h2>').text(location).appendTo($group);
					$('<ul>',{
						'class':'locationItems',
						'group':location
					}).appendTo($group);
				}
			}

			tmp_location_switch = location;

			info = locationGroup.info;

			var $anchor = $('<a>',{
				'href':info.source,
				'class':'jobsite',
				'info':info.title,
				'target':'_blank'
			}).html('<span>' + info.title + '</span>');

			var $row = $('<li>').append($anchor)
			$('ul[group='+location+']',$dateGroup).append($row);

		});

		var $newOrder = $('div[group]',$dateGroup).get();
		$newOrder.sort(function(a , b)
		{
			return $(a).attr('group') > $(b).attr('group')?1:-1;
		});
		$dateGroup.empty();
		$dateGroup.append($newOrder);
//		console.log('blah',$('div[group]',$dateGroup));

	});

	$('#buttons').show();
	$('#search_btn').val('Search Craigslist');
	$('#loader').hide();
	$.getScript('/js/nav.js');

}

function content_size()
{
	$('#content-container').css('left',$('#find_items').innerWidth(true));
	$('#content,#link_content')
	.css('height',$(window).height()-70)
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

function hoverReset()
{
	$('#buttons a.button, #open_windows a.button').removeClass('hover');
	$(this).addClass('hover');
}

function showSearch()
{
	$('#link_content').hide();
	$('#content').show();
	$('#toggle_disp').show();
	$('#show_search').hide();
	$('title').text($(document).data().title);
}

$('.windowLink')
	.live('click',function(event)
	{
		event.preventDefault();
		hoverReset();
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
		showSearch();
	});

$('#region_list_disp')
	.data('show',false)
	.live('click',function()
	{
		if($(this).data().show)
		{
			$(this)
			.text('open')
			.data('show',false);
		}
		else
		{
			$(this)
			.text('close')
			.data('show',true);
		}

		$('#region_list').toggle();
	});

$('#areas_list_disp')
	.data('show',false)
	.live('click',function()
	{
		if($(this).data().show)
		{
			$(this)
			.text('open')
			.data('show',false);
		}
		else
		{
			$(this)
			.text('close')
			.data('show',true);
		}
		$('#areas_list').toggle();
	});



$('#show_search')
	.live('click',function(event)
	{
		event.preventDefault();
		showSearch();
	});

function buildFormList(json)
{
	if($('#areas_list').length)
	{
		$.each(json.area_list,function(i,obj)
		{
			$('<label>',{
				'for':obj.partial
			}).append(
				$('<input>',{
					'class':'region '+obj.type+' location',
					'type':'checkbox',
					'id':obj.partial,
					'name':'include[]',
					'value':obj.partial
				})
				).append(obj.name+', '+obj.state).appendTo('#areas_list');
		});
	}

	if($('#region_list').length)
	{
		$.each(json.region_list,function(i,obj)
		{
			$('<label>',{
				'for':obj.type
			}).append(
				$('<input>',{
					'class':'regions',
					'type':'checkbox',
					'id':obj.type,
					'name':'region[]',
					'value':obj.type
				})
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
				})
				).append(obj.name).appendTo('#region_list');
		});
	}
}

$(function()
{

	$(document)
		.data('title',$('title').text());

	$('#buttons a.button')
		.click(hoverReset);

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

	$('#find_items')
		.submit(function()
		{
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
				url: '/',
				data: $('#find_items').serialize(),
				dataType: 'json',
				success: function(json)
				{
					process_data(json);

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
		.click(function(event)
		{
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
				$('<div>',{
					'id':'mask',
					'css':{
						'background-color':'rgba(0,0,0,.65)',
						'position':'absolute',
						'top':'0',
						'left':'0',
						'z-index':'1020'
					}
				});

			var $load =
				$('<div>',{
					'id':'load',
					'css':{
						'width':'275px',
						'height':'100px',
						'position':'absolute',
						'top':'0',
						'left':'0',
						'z-index':'1050',
						'background-color':'white',
						'border':'solid 1px black',
						'-moz-box-shadow':'0 0 2px black',
						'-webkit-box-shadow':'0 0 2px black'
					}
				})
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
			$('#mask').css({
				'width':maskWidth,
				'height':maskHeight
			});
			centerWindow($load);
		});

});