(function($){
	function float_label(input_type){
		$(input_type).each(function(){
			var $this = $(this);
			var text_value = $(this).val();

			// on focus add class "active" to label
			$this.focus(function(){
				$this.next().addClass('active');
			});

			// on blur check field and remove class if needed
			$this.blur(function(){
				if($this.val() === '' || $this.val() === 'blank'){
					$this.next().removeClass();
				}
			});
					
			// Check input values on postback and add class "active" if value exists
			if(text_value!=''){
				$this.next().addClass('active');
			}
		});
	}
	// Add a class of "float_label" to the input field
	float_label(".float_label");
	
	var get_url_parameter = function (s_param) {
	    var s_page_url = window.location.search.substring(1),
	        s_url_variables = s_page_url.split('&'),
	        s_parameter_name,
	        i;
	
	    for (i = 0; i < s_url_variables.length; i++) {
	        s_parameter_name = s_url_variables[i].split('=');
	
	        if (s_parameter_name[0] === s_param) {
	            return s_parameter_name[1] === undefined ? true : decodeURIComponent(s_parameter_name[1]);
	        }
	    }
	};
	
	var fpage = get_url_parameter('fpage');
	if(fpage){
		$([document.documentElement, document.body]).animate({
	        scrollTop : $('.responsive-table').offset().top - 140
	    }, 1000);
	}
	
	$(document).on('click', '.responsive-table li .feedback-close', function(e){
		var active_list = $(this).parent();
		if(active_list.hasClass('active')){
			active_list.removeClass('active');
			active_list.find('.fid-message').text("");
			return false;
		}
	});
	$(document).on('click', '.responsive-table li.table-row', function(e){
		var active_list = $(this);
		if(active_list.hasClass('active')){
			return false;
		}
		$('.responsive-table li.active').removeClass('active');
		active_list.find('.feedback-loader').show();
		var fid = active_list.data( 'fid' );
		var data = {
			'action' : 'feedback_form_single_data',
			'fid' : fid
		}
		$.ajax({
			url:     feedback.ajaxurl,
			type:    "POST",
			data:    data,
			success: function( response ) {
				active_list.find('.feedback-loader').hide();
				active_list.addClass("active");
				if(response.success){
					if(response.data.message){
						active_list.find('.fid-message strong').show();
						active_list.find('.fid-message').text(response.data.message);
					}
				} else {
					active_list.find('.fid-message strong').hide();
					active_list.find('.fid-message').text(response.data);
				}
			}
	    });
	});
	$(document).on('submit', 'form.feedback-form', function(e){
		e.preventDefault();
		var current_form = $(this);
		var feedback_form = current_form.serialize();
	    $.ajax({
			action:  'feedback_form_data',
			type:    "POST",
			url:     feedback.ajaxurl,
			data:    feedback_form,
			success: function( response ) {
				if(response.success){
					current_form.hide();
					$('.feedback-form-notifications').html('<p class="success-notification">' + response.data + '</p>');
					$('.feedback-form-notifications').show();
				} else {
					$('.feedback-form-notifications').html('<p class="error-notification">' + response.data + '</p>');
					$('.feedback-form-notifications').show();
				}
			}
	    });
	    return false;
	});
})(jQuery);