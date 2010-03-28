$(document).ready(function(){
	$('#search_btn').live('click',function(){
		$('#find_jobs').submit();
		return false;
	});
	$('#change_size').live('click',function(){
		if($('#change_size_container').css('display') == 'block')
		{
			$('#change_size_container').css('display','none');
			$('#find_jobs').animate({width:'25px'},'fast',function(){
				$('#change_size').text('[+]');
				content_size();
			});

		}else{
			$('#find_jobs').animate({width:'250px'},'fast',function(){
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
	$('#find_jobs').submit(function(){
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
			data: $('#find_jobs').serialize(),
			success: function(data){
				$('#content').html(data);
				$('#toggle_disp').show();
				$('#search_btn').val('Search Craigslist');
				$('#loader').hide();
				$.getScript('/js/nav.js');
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
});

function content_size()
{
	$('#content-container').css('left',$('#find_jobs').innerWidth(true));
	$('#content')
		.css('height',$(window).height()-40)
		.css('width',$(window).width() - $('#find_jobs').innerWidth(true)-15)
		.css('margin-left','10px');
}