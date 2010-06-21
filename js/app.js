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
					(config.prepend.length?config.prepend:'<strong>' + $self.text() + '</strong>' + '<br />') +
					$self.attr(config.attr)
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

$(document).ready(function(){

	var process_data = function(json)
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
				output+='<li><a href="' + link + '" class="jobsite" target="_blank"><span>' + info.title + ' : <span style="color:black;">' + info.field + '</span></span></a>' + not_near + '</li>';
			}
			output+='</div>';
		}
		return output;
	}

	var content_size = function()
	{
		$('#content-container').css('left',$('#find_items').innerWidth(true));
		$('#content')
			.css('height',$(window).height()-40)
			.css('width',$(window).width() - $('#find_items').innerWidth(true)-15)
			.css('margin-left','10px');
	}

	$('#search_btn').live('click',function(){
		$('#find_items').submit();
		return false;
	});
	$('#change_size').live('click',function(){
		if($('#change_size_container').css('display') == 'block')
		{
			$('#change_size_container').css('display','none');
			$('#find_items').animate({width:'25px'},'fast',function(){
				$('#change_size').text('[+]');
				content_size();
			});

		}else{
			$('#find_items').animate({width:'250px'},'fast',function(){
				$('#change_size_container').css('display','block');
				content_size();
				$('#change_size').text('[-]');
			});
		}
		return false;
	});
	$('input[type="checkbox"].regions').live('click',function(){
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
	$('#find_items').submit(function(){
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
		$('#content').show().html('Loading...');
		$('#search_btn').val('searching');
		$('#toggle_disp').hide();
		$.ajax({
			type: "POST",
			url: window.PHP_SELF,
			data: $('#find_items').serialize(),
			dataType: 'json',
			success: function(json){
				$('#content').html(process_data(json));
				$('#toggle_disp').show();
				$('#search_btn').val('Search Craigslist');
				$('#loader').hide();
				$.getScript('/js/nav.js');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				console.log(XMLHttpRequest, textStatus, errorThrown);
			}
		});
		return false;
	});
	$('#content h1').live('click',function(){
		$(this).next('div.date').toggle();
	});
	$('#content h2').live('click',function(){
		$(this).next('ul').toggle();
	});
	content_size();
	$(window).resize(content_size);
	$('#donate').click(function(e){
		e.preventDefault();

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

		var modal_layer = document.createElement('div');
		var $modal = $(modal_layer);
		$modal
			.attr('id','mask')
			.css('background-color','rgba(0,0,0,.5)')
			.css('position','absolute')
			.css('top','0').css('left','0')
			.css('z-index','100')
		$('body').prepend($modal);

		var load = document.createElement('div');
		var $load = $(load);
		$load
			.attr('id','load')
			.css('width','275px')
			.css('height','100px')
			.css('position','absolute')
			.css('top','0').css('left','0')
			.css('z-index','110')
			.css('background-color','white')
			.css('border','solid 1px black')
			.css('-moz-box-shadow','0px 0px 2px #000')
			.css('-webkit-box-shadow','0px 0px 2px #000')
			.append(form);

		$('body').prepend($load);

		$modal.click(function(){
			$modal.remove();
			$load.remove();
		});

		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();

		//Set heigth and width to mask to fill up the whole screen
		$('#mask').css({'width':maskWidth,'height':maskHeight});

		//transition effect
//		$('#mask').fadeIn(1000);
//		$('#mask').fadeTo("slow",0.8);

		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();

		//Set the popup window to center
		$load.css('top',  winH/2-$load.height()/2);
		$load.css('left', winW/2-$load.width()/2);

		//transition effect
//		$load.fadeIn(2000);
	})
});

