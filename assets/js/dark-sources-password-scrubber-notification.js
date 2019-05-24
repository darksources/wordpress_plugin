jQuery(document).ready(function($){
    'use strict';
    function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;    
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
    };
    var userID = getUrlParameter('user');
    var darkSourcesNotification = '';
    var html = '\
    <div id="dark-sources-notification-wrap">\
        <div class="flex">\
            <div class="notification-wrap">\
                <span class="dashicons dashicons-no dark-sources-close"></span>\
                <img src="' + darkSourcesPopupNotification.logoUrl + '"/>\
                <hr>\
                <h2>Security Risk Detected!</h2>\
                <div class="dark-sources-notification"></div>\
            </div>\
        </div>\
    </div>\
    ';
    var data = {
        action : 'user_meta_data',
        nonce  : darkSourcesPopupNotification.nonce,
        user_id : userID,
    };
    $.ajax({
        type : 'POST',
        url : darkSourcesPopupNotification.ajaxurl,
        data : data,
    }).success(function(response){
        if(response !== 'no notice found' && response !== 'paid plan required'){
            darkSourcesNotification = response;
            if(darkSourcesNotification.length){
                $(html).hide().appendTo('body').fadeIn('fast');
                $('div.dark-sources-notification').html(darkSourcesNotification);
                data['method'] = 'remove';
                $.ajax({
                    type : 'POST',
                    url : darkSourcesPopupNotification.ajaxurl,
                    data : data,
                }).success(function(response){
                    //success
                }).fail(function(xhr, textStatus, e){
                    console.log('XHR response: '+JSON.stringify(xhr)+' Text Response: '+textStatus+' Error-second: '+e);
                });
            }
        }
    }).fail(function(xhr, textStatus, e){
        console.log('XHR response: '+JSON.stringify(xhr)+' Text Response: '+textStatus+' Error-first: '+e);
    });
    $(document).on('click', '.dark-sources-close', function(){
        $(this).parents().eq(2).fadeOut('fast', function(){
            $(this).remove();
        });
    });
});