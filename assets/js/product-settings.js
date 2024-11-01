( function( $ ) {

	var productSettings = {

		/**
		 * Init
		 * 
		 * @return void
		**/
		init: function()
		{

			// Token
			productSettings.initShowIfToken();

		},

		initShowIfToken: function()
		{

			// Simple
			$( '#general_product_data' ).ready( function( e ) {

				// Product Type : Change
				$( '#product-type' ).on( 'change', function( e ) {

					// Add prop required
					if ( 'simple' === $( this ).val() ) {

						// Toggle by type token
						productSettings.toggleByTypeToken(
							$( '#_ntvwc_type_token' ),
							$( '#_virtual' ),
							$( '.show_if_token' ),
							$( '#_ntvwc_token_type' ),
							$( '#_ntvwc_token_value' ),
							$( '.show_if_validation_token' ),
							$( '.show_if_update_token' )
						);

					}

					else {

						$( '#general_product_data .ntvwc_token_options .show_if_token' ).hide();

					}

				} );

				// Is Token Type
				$( '#_ntvwc_type_token' ).on( 'change', function( e ) {

					// Toggle by type token
					productSettings.toggleByTypeToken(
						$( '#_ntvwc_type_token' ),
						$( '#_virtual' ),
						$( '.show_if_token' ),
						$( '#_ntvwc_token_type' ),
						$( '#_ntvwc_token_value' ),
						$( '.show_if_validation_token' ),
						$( '.show_if_update_token' )
					);

				} );

				// Token Type : Change
				$( '#_ntvwc_token_type' ).on( 'change', function( e ) {

					// Toggle by type token
					productSettings.toggleByTypeToken(
						$( '#_ntvwc_type_token' ),
						$( '#_virtual' ),
						$( '.show_if_token' ),
						$( '#_ntvwc_token_type' ),
						$( '#_ntvwc_token_value' ),
						$( '.show_if_validation_token' ),
						$( '.show_if_update_token' )
					);

				} );

				// Hashed Token Value
				$( '.get-the-hashed-value' ).on( 'click', function( e ) {
					var $targetInput = $( this ).parent().find( '.hashed-token-value' );
					ntvwc.copyTextToClipboard( $targetInput.val() );
					//console.log( $targetInput.val() );
				} );

				// Toggle by type token
				productSettings.toggleByTypeToken(
					$( '#_ntvwc_type_token' ),
					$( '#_virtual' ),
					$( '.show_if_token' ),
					$( '#_ntvwc_token_type' ),
					$( '#_ntvwc_token_value' ),
					$( '.show_if_validation_token' ),
					$( '.show_if_update_token' )
				);

			} );

		},

		/**
		 * Should Require
		 * @param  {[type]} $typeToken            [description]
		 * @param  {[type]} $typeVirtual          [description]
		 * @param  {[type]} $showIfTypeToken      [description]
		 * @param  {[type]} $tokenType            [description]
		 * @param  {[type]} $requiredIfValidation [description]
		 * @param  {[type]} $showIfValidation     [description]
		 * @param  {[type]} $showIfUpdate         [description]
		 * @return {[type]} [description]
		**/
		toggleByTypeToken: function(
			$typeToken,
			$typeVirtual,
			$showIfTypeToken,
			$tokenType,
			$requiredIfValidation,
			$showIfValidation,
			$showIfUpdate
		)
		{

			if ( $typeToken.prop( 'checked' ) ) {

				$( $showIfTypeToken ).show();

				// Token Type
				productSettings.toggleByTokenType(
					$tokenType,
					$showIfTypeToken,
					$requiredIfValidation,
					$showIfValidation,
					$showIfUpdate
				);

			} else {

				$requiredIfValidation.prop( 'required', false ).change();
				$( $showIfTypeToken ).hide();

			}

		},

			/**
			 * [toggleByTokenType description]
			 * @param  {[type]} $tokenType            [description]
			 * @param  {[type]} $showIfTypeToken      [description]
			 * @param  {[type]} $requiredIfValidation [description]
			 * @param  {[type]} $showIfValidation     [description]
			 * @param  {[type]} $showIfUpdate         [description]
			 * @return {[type]}                       [description]
			**/
			toggleByTokenType: function( $tokenType, $showIfTypeToken, $requiredIfValidation, $showIfValidation, $showIfUpdate )
			{

				$showIfTypeToken.hide();
				if ( 'validation' === $tokenType.val() ) {
					$requiredIfValidation.prop( 'required', true ).change();
					$showIfUpdate.hide();
					$showIfValidation.show();
				} else if ( 'update' === $tokenType.val() ) {
					$requiredIfValidation.prop( 'required', false ).change();
					$showIfValidation.hide();
					$showIfUpdate.show();
				} else {
					$requiredIfValidation.prop( 'required', false ).change();
					$showIfValidation.hide();
					$showIfUpdate.hide();
				}

			},

	};

	$( function() {

		productSettings.init();

	});
	
}) ( jQuery );