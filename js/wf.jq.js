/**
 * @package WorldFlags
 */

function insertFlag(into, img, size, txt, show, ajax){
	if (ajax) {
		jQuery.ajax({
			type: "POST",
			url: wf_base_url+"flag.php",
			dataType: 'json',
			data: "size="+size+"&.rand=" + Math.floor(Math.random()*100001), 
			success: function(msg){
						jQuery(into).html('<img class="wf-img" src="'+msg.src+'" alt="'+msg.country+'" title="'+msg.country+'" />'
							+ (show == 'yes' || show == '1' ? '<span class="wf-text">'+msg.country+'</span>' : ''));
					},
			error : function(msg){
						alert ("Error loading data.\n\n"+msg.responseText); 
					  }
		});

	} else {
		jQuery(into).html('<img class="wf-img" src="'+wf_base_url+size+'/'+img+'" alt="'+txt+'" title="'+txt+'" />'
							+ (show == 'yes' || show == '1' ? '<span class="wf-text">'+txt+'</span>' : ''));
	}
}
