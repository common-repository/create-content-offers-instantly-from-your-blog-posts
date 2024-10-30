(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	
	$(document).ready(function() {

		if ($('.cco_offer').length) {
			var post_id = $('.cco_offer').data('post_id').toString();	
		} else {
			var post_id = '0';
		}

		var cco_checklist = [];
		cco_checklist[post_id] = [];
		$('.cco_offer input[type="checkbox"]').each(function(){
			var checkbox_id = $(this).data('checkbox_id');
			if ($(this).is(':checked')) {
		 		cco_checklist[post_id][checkbox_id] = 1;
		 	} else {
		 		cco_checklist[post_id][checkbox_id] = 0;
		 	}
		});

		//store results of checkbox in cookie
		$('.cco_offer input[type="checkbox"]').change(function(){

		 	//get the unique identifier for the checkbox
		 	var values = $(this).attr('id').split('-');
		 	var checkbox_id = $(this).data('checkbox_id');

		 	//save checked status
		 	if ($(this).is(':checked')) {
		 		cco_checklist[post_id][checkbox_id] = 1;
		 	} else {
		 		cco_checklist[post_id][checkbox_id] = 0;
		 	}

		 	//array to string then save
			setCookie('cco_checklist', JSON.stringify(cco_checklist));
		});

	});



	/**
	 * Cookie get function
	 * @param cname
	 * @returns {*}
	 */
	function getCookie(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}

	/**
	 * Cookie create function
	 *
	 * @param cname Cookie Name
	 * @param cvalue Cookie Value
	 * @param exdays Expires Date
	 */
	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

})( jQuery );
