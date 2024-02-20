function wams_sanitize_value( value, el ) {
	var element = document.createElement( 'div' );
	element.innerText = value;
	var sanitized_value = element.innerHTML;
	if ( el ) {
		jQuery( el ).val( sanitized_value );
	}

	return sanitized_value;
}

function wams_unsanitize_value( input ) {
	var e = document.createElement( 'textarea' );
	e.innerHTML = input;
	// handle case of empty input
	return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
}


jQuery(document).ready(function() {
    jQuery( document.body ).on('click', '#wams-search-button', function() {
        var action = jQuery(this).parents('.wams-search-form').data('search_page');
    
        var search_keys = [];
        jQuery(this).parents('.wams-search-form').find('input[name="wams-search-keys[]"]').each( function() {
            search_keys.push( jQuery(this).val() );
        });
    
        var search = jQuery(this).parents('.wams-search-form').find('.wams-search-field').val();
    
        var url;
        if ( search === '' ) {
            url = action;
        } else {
            var query = '?';
            for ( var i = 0; i < search_keys.length; i++ ) {
                query += search_keys[i] + '=' + search;
                if ( i !== search_keys.length - 1 ) {
                    query += '&';
                }
            }
    
            url = action + query;
        }
        window.location = url;
        alert(url);
    });

	//make search on Enter click
	jQuery( document.body ).on( 'keypress', '.wams-search-field', function(e) {
		if ( e.which === 13 ) {
			var action = jQuery(this).parents('.wams-search-form').data('members_page');

			var search_keys = [];
			jQuery(this).parents('.wams-search-form').find('input[name="wams-search-keys[]"]').each( function() {
				search_keys.push( jQuery(this).val() );
			});

			var search = jQuery(this).val();

			var url;
			if ( search === '' ) {
				url = action;
			} else {
				var query = '?';
				for ( var i = 0; i < search_keys.length; i++ ) {
					query += search_keys[i] + '=' + search;
					if ( i !== search_keys.length - 1 ) {
						query += '&';
					}
				}

				url = action + query;
			}
			window.location = url;
		}
	});


    jQuery( document.body ).on( 'click', '.wams-ajax-paginate', function( e ) {
		e.preventDefault();

		var obj = jQuery(this);
		var parent = obj.parent();
		parent.addClass( 'loading' );

		var pages = obj.data('pages')*1;
		var next_page = obj.data('page')*1 + 1;

		var hook = obj.data('hook');

		if ( 'wams_load_posts' === hook ) {

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					action: 'wams_ajax_paginate_posts',
					author: jQuery(this).data('author'),
					page:   next_page,
					nonce: wams_scripts.nonce
				},
				complete: function() {
					parent.removeClass( 'loading' );
				},
				success: function( data ) {
					parent.before( data );
					if ( next_page === pages ) {
						parent.remove();
					} else {
						obj.data( 'page', next_page );
					}
				}
			});
		} else if ( 'wams_load_comments' === hook ) {

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					action: 'wams_ajax_paginate_comments',
					user_id: jQuery(this).data('user_id'),
					page: next_page,
					nonce: wams_scripts.nonce
				},
				complete: function() {
					parent.removeClass( 'loading' );
				},
				success: function( data ) {
					parent.before( data );
					if ( next_page === pages ) {
						parent.remove();
					} else {
						obj.data( 'page', next_page );
					}
				}
			});
		} else {
			var args = jQuery(this).data('args');
			var container = jQuery(this).parents('.um.wams-profile.wams-viewing').find('.wams-ajax-items');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
				data: {
					action: 'wams_ajax_paginate',
					hook: hook,
					args: args,
					nonce: wams_scripts.nonce
				},
				complete: function() {
					parent.removeClass( 'loading' );
				},
				success: function(data){
					parent.remove();
					container.append( data );
				}
			});
		}
	});


    jQuery(document).on('click', '.wams-ajax-action', function( e ) {
		e.preventDefault();
		var hook = jQuery(this).data('hook');
		var user_id = jQuery(this).data('user_id');
		var arguments = jQuery(this).data('arguments');

		if ( jQuery(this).data('js-remove') ){
			jQuery(this).parents('.'+jQuery(this).data('js-remove')).fadeOut('fast');
		}

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'wams_muted_action',
				hook: hook,
				user_id: user_id,
				arguments: arguments,
				nonce: wams_scripts.nonce
			},
			success: function(data){

			}
		});
		return false;
	});

});
