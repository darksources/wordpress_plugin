jQuery(document).ready(function($){
	'use strict';
	//number format with , on keyup
	$('textarea').hide();
    var optionName;
    var message;
	var modal = '\
	<div class="dark-sources-opacity">\
        <div class="input-wrap">\
        <span class="dashicons dashicons-no dark-sources-custom-close"></span>\
			<textarea placeholder="Please enter your custom plain text here. Clear all text to restore default messages." class="text-pop-up"></textarea>\
			<div class="custom-message-button-wrap">\
				<button class="button button-primary clickable-clear dark-sources-clear-message">CLEAR MESSAGE</button>\
				<button class="button button-primary clickable-save dark-sources-custom-message-save-button">SAVE MESSAGE</button>\
			</div>\
		</div>\
	</div>';
	$('#dark-sources-settings .custom-trigger').click(function(e){
        e.preventDefault();
            if($(this).hasClass('paid')){
            optionName = $(this).parents('.input-wrap').next('.textarea-wrap').children().attr('id');
            var textAreaValue = $(this).parents('.input-wrap').next('.textarea-wrap').children().val();
            $('#wpwrap').append(modal);
            if(textAreaValue.length){
                $('.text-pop-up').val(textAreaValue);
            }
        }
	});
	//save message
	$(document).on('click', '.dark-sources-opacity .clickable-save', function(e){
		e.preventDefault();
		$('.dark-sources-custom-message-save-button').text('SAVING...').removeClass('clickable-save');
		//get input text
		message = $(this).parent().siblings('textarea').val();
		//save message via ajax
		var data = {
			action : 'save_custom_message',
			nonce  : darkSourcesCustomMessage.nonce,
			message : message,
			option_name : optionName,
		};
		console.log(data.message);
		$.ajax({
			type : 'POST',
			url : darkSourcesCustomMessage.ajaxurl,
			data : data,
		}).success(function(response){
			console.log(response);
			if(response === 'CUSTOM MESSAGE SAVED!' ){
                $('#'+optionName).val(message);
			}
			$('.dark-sources-custom-message-save-button').text(response);
			setTimeout(function(){
				$('.dark-sources-custom-message-save-button').text('SAVE MESSAGE').addClass('clickable-save');
			},5000);
		}).fail(function(xhr, textStatus, e){
			console.log('XHR response: '+JSON.stringify(xhr)+' Text Response: '+textStatus+' Error-first: '+e);
		});
	});
	//clear message
	$(document).on('click', '.dark-sources-clear-message', function(e){
		e.preventDefault();
		$('.dark-sources-clear-message').text('CLEARING...').removeClass('clickable-clear');
		//set text to empty
		message = '';
		$(this).parent().siblings('textarea').val(message);
		//save message via ajax
		var data = {
			action : 'save_custom_message',
			nonce  : darkSourcesCustomMessage.nonce,
			message : message,
			option_name : optionName,
		};
		$.ajax({
			type : 'POST',
			url : darkSourcesCustomMessage.ajaxurl,
			data : data,
		}).success(function(response){
			console.log(response);
			if(response === 'CUSTOM MESSAGE SAVED!' ){
                $('#'+optionName).val(message);
			}
			$('.dark-sources-clear-message').text(response);
			setTimeout(function(){
				$('.dark-sources-clear-message').text('CLEAR MESSAGE').addClass('clickable-clear');
			},5000);
		}).fail(function(xhr, textStatus, e){
			console.log('XHR response: '+JSON.stringify(xhr)+' Text Response: '+textStatus+' Error-first: '+e);
		});
    });
    $(document).on('click', '.dark-sources-custom-close', function(){
        $(this).parents().eq(1).fadeOut('fast', function(){
            $(this).remove();
        });
    });
    $(document).keyup(function(e){
        if (e.keyCode == 27) {
            $('.dark-sources-opacity').remove();
        }
    });
});
