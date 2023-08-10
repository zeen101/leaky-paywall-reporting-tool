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
					$('#leaky-paywall-reporting-tool-message').text('Do not close this window.  Large datasets can take a while to process.');

					const formData = $("#leaky-paywall-reporting-tool-form").serialize();

					const data = {
						formData: formData,
					};

					const rand = Math.floor(Math.random() * 1001);

					self.process_step( 1, data, self, rand );

				});

			},

			process_step : function( step, data, self, rand ) {

				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'leaky_paywall_reporting_tool_process',
						step: step,
						rand: rand,
						formData: data.formData,
					},
					dataType: 'json',
					success: function( response ) {

						if ( 'done' == response.step ) {

							console.log('done - download');
							$('#leaky-paywall-reporting-tool-submit').val('Processing complete');

							if ( response.url == 'none' ) {
								console.log('No subscribers match your parameters.');
								$('#leaky-paywall-reporting-tool-submit').val('Generate Report');
								$('#leaky-paywall-reporting-tool-submit').attr('disabled', false );
								$('#leaky-paywall-reporting-tool-message').text('No subscribers match your parameters.');
							} else {

							 	window.location = response.url;
								$('#leaky-paywall-reporting-tool-submit').val('Generate Report');
								$('#leaky-paywall-reporting-tool-submit').attr('disabled', false );
								$('#leaky-paywall-reporting-tool-message').text('Processing complete.');
							}

						} else {

							console.log('continue processing');
							self.process_step( parseInt( response.step ), data, self, rand );

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