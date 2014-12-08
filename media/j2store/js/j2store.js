/**
 * Setup (required for Joomla! 3)
 */
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}

(function($) {
$(document).ready(function(){
	
	if ($('#j2store_shipping_make_same').length > 0) {
		if ($('#j2store_shipping_make_same').is(':checked')) {
			$('#j2store_shipping_section').css({'visible' : 'visible', 'display' : 'none'});
			
			$('#j2store_shipping_section').children(".input-label").removeClass("required");
					
			$('#j2store_shipping_section').children(".input-text").removeClass("required");
		}
	}
	
	$('.j2storeCartForm1').each(function(){
	$(this).submit(function(e) {	
		e.preventDefault();
		var form = $(this);
		
		/* Get input values from form */
		var values = form.serializeArray();
		
	
	$.ajax({
		url: j2storeURL+'index.php',
		type: 'post',
		//data: form.find('input[type=\'text\']'), form.find('input[type=\'hidden\']'), form.find('input[type=\'radio\']:checked'), form.find('input[type=\'checkbox\']:checked'), form.find('select'), form.find('textarea'),
		data: values,
		dataType: 'json',
		success: function(json) {
			form.find('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
			$('.j2store-notification').hide();
			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {
						form.find('#option-' + i).after('<span class="j2error">' + json['error']['option'][i] + '</span>');
					}
				}
				if (json['error']['stock']) {
					form.find('.j2stock').after('<span class="j2error">' + json['error']['stock'] + '</span>');
				}
				
				if (json['error']['product']) {
					form.find('.j2product').after('<span class="j2error">' + json['error']['product'] + '</span>');
				}
				
			}
			
			if (json['redirect']) {
				window.location.href = json['redirect'];
			}
			
			if (json['success']) {
				form.find('.j2store-notification .message').html('<div class="j2success">' + json['successmsg'] + '</div>');
				if (!json['redirect']) {
					form.find('.j2store-notification').fadeIn('slow').delay(2000).fadeOut('slow');
				}
			
			//if module is present, let us update it.
				if($('#miniJ2StoreCart').length > 0) {
					$('#miniJ2StoreCart').html(json['total']);
				}
				doMiniCart();
			 
			}	
		}
	});

	});
	});
	
});
})(j2store.jQuery);

function doMiniCart() {
(function($) {		
	var container = '#miniJ2StoreCart';
	var murl = j2storeURL
			+ 'index.php?option=com_j2store&view=mycart&task=ajaxmini';
	
	if ($('#miniJ2StoreCart').length > 0) {
	$.ajax({
		url : murl,
		type: 'post',
		success: function(response){
			if ($('#miniJ2StoreCart').length > 0) {
				$('#miniJ2StoreCart').html(response);
			}
		}

	});
	}
	
	var detailurl = j2storeURL
	+ 'index.php?option=com_j2store&view=mycart&task=displayCart';
	if ($('#detailJ2StoreCart').length > 0) {
		$.ajax({
			url : detailurl,
			type: 'post',
			 cache: false,             
			success: function(response){				
					$('#detailJ2StoreCart').html(response);				
			}

		});
	}
	
})(j2store.jQuery);
	
}

function j2storeGetPaymentForm(element, container) {
	(function($) {	
	var url = j2storeURL+ 'index.php?option=com_j2store&view=checkout&task=getPaymentForm&tmpl=component&payment_element='+ element;
	j2storeDoTask(url, container, document.adminForm);
	})(j2store.jQuery);
}

function j2storeDoTask(url, container, form, msg) {

	(function($) {		
	//to make div compatible
	container = '#'+container;	

	// if url is present, do validation
	if (url && form) {
		var str = $(form).serialize();
		// execute Ajax request to server
		$.ajax({
			url : url,
			type : 'post',
			 cache: false,
             contentType: 'application/json; charset=utf-8',
             dataType: 'json',
             beforeSend: function() {
               	 $(container).before('<span class="wait"><img src="media/j2store/images/loader.gif" alt="" /></span>');
                   },
             complete: function() {
            	 $('.wait').remove();
             },
			// data:{"elements":Json.toString(str)},
             success: function(json) {
            	if ($(container).length > 0) {            		
            		$(container).html(json.msg);
				}				
				return true;
			}
		});
	} else if (url && !form) {
		// execute Ajax request to server
		$.ajax({
			url : url,
			 type: 'post',
             cache: false,
             contentType: 'application/json; charset=utf-8',
             dataType: 'json',
             beforeSend: function() {
               	 $(container).before('<span class="wait"><img src="media/j2store/images/loader.gif" alt="" /></span>');
                 },
             complete: function() {
            	 $('.wait').remove();
             	},
             success: function(json) {
            	 if ($(container).length > 0) {
            		$(container).html(json.msg);
				}				
			}
		});
	}
	})(j2store.jQuery);
}

/**
 * 
 * @param {String}
 *            msg message for the modal div (optional)
 */
function j2storeNewModal(msg) {
	if (typeof window.innerWidth != 'undefined') {
		var h = window.innerHeight;
		var w = window.innerWidth;
	} else {
		var h = document.documentElement.clientHeight;
		var w = document.documentElement.clientWidth;
	}
	var t = (h / 2) - 15;
	var l = (w / 2) - 15;
	var i = document.createElement('img');
	var src = j2storeURL + 'media/j2store/images/ajax-loader.gif';
	i.src = src;

	// var s = window.location.toString();
	// var src = 'components/com_j2store/images/ajax-loader.gif';
	// i.src = (s.match(/administrator\/index.php/)) ? '../' + src : src;

	i.style.position = 'absolute';
	i.style.top = t + 'px';
	i.style.left = l + 'px';
	i.style.backgroundColor = '#000000';
	i.style.zIndex = '100001';
	var d = document.createElement('div');
	d.id = 'j2storeModal';
	d.style.position = 'fixed';
	d.style.top = '0px';
	d.style.left = '0px';
	d.style.width = w + 'px';
	d.style.height = h + 'px';
	d.style.backgroundColor = '#000000';
	d.style.opacity = 0.5;
	d.style.filter = 'alpha(opacity=50)';
	d.style.zIndex = '100000';
	d.appendChild(i);
	if (msg != '' && msg != null) {
		var m = document.createElement('div');
		m.style.position = 'absolute';
		m.style.width = '200px';
		m.style.top = t + 50 + 'px';
		m.style.left = (w / 2) - 100 + 'px';
		m.style.textAlign = 'center';
		m.style.zIndex = '100002';
		m.style.fontSize = '1.2em';
		m.style.color = '#ffffff';
		m.innerHTML = msg;
		d.appendChild(m);
	}
	document.body.appendChild(d);
}

function j2storeCartRemove(key, product_id, pop_up) {
	(function($) {
	
	var container;
	if (pop_up == 1) {
		container = $('#sbox-content');
	} else {
		container = $('#j2storeCartPopup');
	}
	var myurl = j2storeURL+'index.php?option=com_j2store&view=mycart&task=update&popup='+ pop_up;
	$.ajax({
				url : myurl,
				type: 'post',
				data : "remove=1&key=" + key,
				//update : container,
				success: function(response) {
					$(container).html(response);
					if ($('#miniJ2StoreCart').length > 0) {
						doMiniCart();
					}
				},// onSuccess
				error: function() {
					window.location(j2storeURL+"index.php?option=com_j2store&view=mycart&task=update&remove=1&cid["+ key + "]=" + product_id);
				}// onFailure
			});
			
	})(j2store.jQuery);
}

function j2storeCartRemoveCoupon() {
	(function($) {	
	var container = $('#j2storeCartPopup');
	
	var myurl = j2storeURL+'index.php?option=com_j2store&view=mycart&task=update&removeCoupon=1&popup=2';
	$.ajax({
				url : myurl,
				type: 'post',				
				//update : container,
				success: function(response) {
					$(container).html(response);					
					doMiniCart();
					
				},// onSuccess
				error: function() {
					window.location(j2storeURL+"index.php?option=com_j2store&view=mycart");
				}// onFailure
			});
			
	})(j2store.jQuery);
}



function j2storeGetAjaxZone(field_name, field_id, country_value, default_zid) {

(function($) {
	var url = j2storeURL
			+ 'index.php?option=com_j2store&view=checkout&task=ajaxGetZoneList';
	$.ajax({
		url : url,
		//update : field_name.substring(0, 4) + 'ZoneList',
		type : 'post',
		data : {
			'country_id' : country_value,
			'zone_id' : default_zid,
			'field_name' : field_name,
			'field_id' : field_id
		},
		// onRequest: function() { document.id('listproduct').set('text',
		// 'loading...'); },
		success: function(response) {
			// document.id('zoneList').
			$('#'+field_name.substring(0, 4) + 'ZoneList').html(response);
		}
	});
})(j2store.jQuery);
}



function j2storeSetShippingRate(name, price, tax, extra, code, combined )
{
	
(function($) {
	$("input[type='hidden'][name='shipping_name']").val(name);
	$("input[type='hidden'][name='shipping_code']").val(code);
	$("input[type='hidden'][name='shipping_price']").val(price);
	$("input[type='hidden'][name='shipping_tax']").val(tax);
	$("input[type='hidden'][name='shipping_extra']").val(extra);	
})(j2store.jQuery);

}