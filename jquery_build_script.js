$('#list a').data('body','');
$('#list a').each(function(){
	$('#list a').data().body+=
		"<location>\n" +
			"\t<state>WA</state>\n" +
			"\t<type>washington</type>\n" +
			"\t<url><![CDATA["+$(this).attr('href')+"/search/jjj?]]></url>\n" +
			"\t<partial>"+$(this).attr('href').replace('http://','').replace('/','')+"</partial>\n" +
			"\t<name><![CDATA["+$(this).text()+"]]></name>\n" +
		"</location>\n";
});
console.log($('#list a').data().body)