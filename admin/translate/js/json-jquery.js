$(document).ready(function(){ 
    //alert(document.getElementById('txtLang').value);
	//attach a jQuery live event to the button
	//$('#loader').ajaxLoader();
	
	function translate(){
		var text = $('#inquiryContent').text();
    	var lang = document.getElementById('txtLang').value;

		var translate_url = '/admin/translate/v2/?text='+ encodeURIComponent(text) +'&to='+lang;
		$.get(translate_url, function(data) {
			console.log(data);
		    $('#showdata').html(data);
			//alert(data); //uncomment this for debug
			//alert (data.item1+" "+data.item2+" "+data.item3); //further debug
			// if(data.translation)
			// $('#showdata').html(data.translation);
			// else
			// $('#showdata').html("Error ="+data.errorReason);			
			//$('#loader').ajaxLoaderRemove();
		});
	}
	translate();
	$('#getdata-button').live('click', function(){
		translate();
		
	});
	
	/** 
	$(".wrap").hover(function() {
    //$('#loader').ajaxLoader();
		//alert('test');
    	var text = $('#inquiryContent').text();
    	var lang = document.getElementById('txtLang').value;
    	var json_url = '/admin/translate/translator.php?text='+ encodeURIComponent(text) +'&to='+lang+'&from=';	  
		//alert(json_url);  
		$.getJSON(json_url, function(data) {	
		    
			//alert(data); //uncomment this for debug
			//alert (data.item1+" "+data.item2+" "+data.item3); //further debug
			if(data.translation)
			$('#showdata').html(data.translation);
			else
			$('#showdata').html("Error ="+data.errorReason);			
			//$('#loader').ajaxLoaderRemove();
		});

		var en_json_url = '/admin/translate/translator.php?text='+ encodeURIComponent(text) +'&to=en&from=';
		$.getJSON(en_json_url, function(data) {	
			if(data.translation)
				$('#showendata').html(data.translation);
			else
				$('#showendata').html("Error ="+data.errorReason);			
		});

	});
	*/
	   

});




