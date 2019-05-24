jQuery(document).ready(function($){
	'use strict';
	//number format with , on keyup
	$('.format-number').keyup(function() {
		var value = $(this).val().replace(/,/g, '');
		value = value.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
		$(this).val(value);
	});
	//trigger keyup for initial page load
	$('.format-number').keyup();

	//slider
	'use strict';
	var container = $('#dark-sources-settings #subscription-wrap');
	var heigthArray = [];
	$('#subscription-wrap .plan').each(function(){
		heigthArray.push(parseInt($(this).outerHeight()));
	});
	function indexOfMax(heigthArray) {
		if (heigthArray.length === 0) {
			return -1;
		}	
		var max = heigthArray[0];
		var maxIndex = 0;
			for (var i = 1; i < heigthArray.length; i++) {
			if (heigthArray[i] > max) {
				maxIndex = i;
				max = heigthArray[i];
			}
		}
		return maxIndex;
	}
	var tallestItemIndex = indexOfMax(heigthArray);
	$('#subscription-wrap .plan').css({
		'width':'100%',
		'position':'absolute',
		'opacity':'0',
		'z-index':'-1',
		'margin-left':'auto',
		'margin-right':'auto',
		'box-sizing':'border-box',
	});
	$(container).css({
        'position':'relative',
        'overflow':'0',
        'flex-wrap':'nowrap',
	});
	var count = $('#subscription-wrap .plan').length;
	$('#plan-range-slider').attr('max', count);
	$('#subscription-wrap .plan').addClass('sliderPlanItem');
	//create height placeholder
	$($('#subscription-wrap .plan').get(tallestItemIndex)).clone().appendTo(container).css({
		'position':'relative',
		'z-index': '-1'
	}).removeClass('sliderPlanItem');

    //show slider that matches range slider value +1
	$('.sliderPlanItem').first().css({
		'opacity':'1',
		'z-index':'1',
	});
	$('#plan-range-slider').on('input', function(){
		var slideNumber = parseInt($(this).val())-1;
		var slide = $('.sliderPlanItem').eq(slideNumber);
		$('.sliderPlanItem').stop(true, true).css({
			'opacity': '0',
			'z-index': '-1' 
		})
		slide.stop(true, true).css({
			'opacity':'1',
			'z-index': '1'
		});
	})
});