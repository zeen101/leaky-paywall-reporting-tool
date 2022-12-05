var $leaky_paywall_reporting_tool = jQuery.noConflict();

$leaky_paywall_reporting_tool(document).ready(function($) {
	
	$( '#expire-start' ).datepicker({
		prevText: '',
		nextText: '',
		dateFormat: $( 'input[name=date_format]' ).val()
	});
	
	$( '#expire-end' ).datepicker({
		prevText: '',
		nextText: '',
		dateFormat: $( 'input[name=date_format]' ).val()
	});

	$( '#created-start' ).datepicker({
		prevText: '',
		nextText: '',
		dateFormat: $( 'input[name=date_format]' ).val()
	});
	
	$( '#created-end' ).datepicker({
		prevText: '',
		nextText: '',
		dateFormat: $( 'input[name=date_format]' ).val()
	});
	
});