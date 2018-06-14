$(document).ready(function(){ 
    //alert(document.getElementById('txtLang').value);
	//attach a jQuery live event to the button
	//$('#loader').ajaxLoader();
	$('#getdata-button').live('click', function(){
    $('#loader').ajaxLoader();
    	var text = document.getElementById('txtString').value
    	var lang = document.getElementById('txtLang').value;
    	var json_url = 'translator.php?text='+ text +'&to='+lang;	    
		$.getJSON(json_url, function(data) {	
		    
			//alert(data); //uncomment this for debug
			//alert (data.item1+" "+data.item2+" "+data.item3); //further debug
			if(data.translation)
			$('#showdata').html("<p class='bdr'>"+data.translation+"</p>");
			else
			$('#showdata').html("<p>Error ="+data.errorReason+"</p>");			
			$('#loader').ajaxLoaderRemove();
		});
	});
    
    $("textarea").blur(function() {
        $('#loader').ajaxLoader();
    	var text = document.getElementById('txtString').value
    	var lang = document.getElementById('txtLang').value;
    	var json_url = 'translator.php?text='+ text +'&to='+lang;	    
		$.getJSON(json_url, function(data) {	
		    
			//alert(data); //uncomment this for debug
			//alert (data.item1+" "+data.item2+" "+data.item3); //further debug
			if(data.translation)
			$('#showdata').html("<p class='bdr'>"+data.translation+"</p>");
			else
			$('#showdata').html("<p>Error ="+data.errorReason+"</p>");			
			$('#loader').ajaxLoaderRemove();
		});
    });	    
    
    $("#txtLang").bind("change", function() {
        $('#loader').ajaxLoader();
    	var text = document.getElementById('txtString').value
    	var lang = document.getElementById('txtLang').value;
    	var json_url = 'translator.php?text='+ text +'&to='+lang;	    
		$.getJSON(json_url, function(data) {	
		    
			//alert(data); //uncomment this for debug
			//alert (data.item1+" "+data.item2+" "+data.item3); //further debug
			if(data.translation)
			$('#showdata').html("<p class='bdr'>"+data.translation+"</p>");
			else
			$('#showdata').html("<p>Error ="+data.errorReason+"</p>");			
			$('#loader').ajaxLoaderRemove();
		});
    });	
});




