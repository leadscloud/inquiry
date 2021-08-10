$(document).ready(function(){ 

	function translate(){
		var text = $('#inquiryContent').text();
    var lang = document.getElementById('txtLang').value;

		var translate_url = '/admin/translate/v3/?text='+ encodeURIComponent(text) +'&to='+lang;
		$.get(translate_url, function(data) {
			let [result] = data;
		  $('#showdata').html(result["translations"][0]["text"]);
		});
	}
	
	translate();

	$('#getdata-button').live('click', function(){
		translate();
	});   

});




