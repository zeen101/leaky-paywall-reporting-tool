( function( $ )  {

	$(document).ready( function() {

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

		///

		var Leaky_Paywall_Reporting_Tool_Bulk = {

			init : function() {
				this.submit();
			},

			submit : function() {

				var self = this;

				$('#leaky-paywall-reporting-tool-submit').on('click', function(e) {

					e.preventDefault();

					$('#leaky-paywall-reporting-tool-submit').val('Processing...please wait');
					$('#leaky-paywall-reporting-tool-submit').attr('disabled', true );

					// $('#endo-bulk-change-subscription-loading-spinner').addClass('subscribers-processing');
					// $('#endo-bulk-change-subscription-wrapper').html('<span>Processing...</span>');
					// $('#endo-bulk-change-subscription-loading-spinner').after('<div class="endo-bulk-change-subscription-progress"><div></div></div>');

					var email = $('#subscriber_email').val();
					var subscriber_level = $('#subscriber_level').children('option:selected').val();
					var product_id = $('#product_id').val();
					var subscriber_note = $('#subscriber_note').val();

					const formData = $("#leaky-paywall-reporting-tool-form").serialize();

					const data = {
						formData: formData,
						// nonce: leaky_paywall_validate_ajax.register_nonce
					};

					self.process_step( 1, data, self );

				});

			},

			process_step : function( step, data, self ) {

				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'leaky_paywall_reporting_tool_process',
						step: step,
						formData: data.formData,
					},
					dataType: 'json',
					success: function( response ) {

						console.log(response);

						if ( 'done' == response.step ) {

							console.log('done - download');

							$('#leaky-paywall-reporting-tool-submit').val('Processing complete');


							window.location = response.url;



							// $( "#endo-bulk-change-subscription-wrapper" ).after( "<div class='notice notice-success'><p>Subscribers Processed</p></div>" ).remove();
							// $('#endo-bulk-change-subscription-loading-spinner').removeClass('subscribers-processing');
							// $('.endo-bulk-change-subscription-progress').remove();

						} else {

							console.log('continue processing');

							// $('.endo-bulk-change-subscription-progress div').animate({
							// 	width: response.percentage + '%',
							// }, 50, function() {
							// 	// animation complete
							// });

							self.process_step( parseInt( response.step ), data, self );

						}

					}
				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				});

			}
		};

		Leaky_Paywall_Reporting_Tool_Bulk.init();

	});

})( jQuery );