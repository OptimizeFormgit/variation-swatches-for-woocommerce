;(function ( $ ) {
	'use strict';

	/**
	 * @TODO Code a function that calculate available combination instead of use WC hooks
	 */
	$.fn.tawcvs_variation_swatches_form = function () {
		return this.each( function() {
			var $form = $( this );

			$form
				.addClass( 'swatches-support' )
                .data("product_variations", $form.find(".tawcvs-available-product-variation").data("product_variations"))
                .trigger('reload_product_variations')
				.on( 'click', '.swatch', function ( e ) {
					e.preventDefault();

					var $el = $( this ),
						$select = $el.closest( '.value' ).find( 'select' ),
						value = $el.attr( 'data-value' );

					if ( $el.hasClass( 'disabled' ) ) {
						return;
					}

					//Disabling other swatches, then resetting the select value to empty
					$el.parents('.tawcvs-swatches').find(".swatch.selected").each(function(){
						$(this).not($el).removeClass("selected");
						$select.val( '' );
					})

					// For old WC
					$select.trigger( 'focusin' );

					// Check if this combination is available
					if ( ! $select.find( 'option[value="' + value + '"]' ).length ) {
						$el.siblings( '.swatch' ).removeClass( 'selected' );
						$select.val( '' ).change();
						$form.trigger( 'tawcvs_no_matching_variations', [$el] );
						return;
					}

					if ( $el.hasClass( 'selected' ) ) {
						$select.val( '' );
						$el.removeClass( 'selected' );
					} else {
						$el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
						$select.val( value );
					}

					$select.change();
				} )
				.on( 'click', '.reset_variations', function () {
					$form.find( '.swatch.selected' ).removeClass( 'selected' );
					$form.find( '.swatch.disabled' ).removeClass( 'disabled' );
				} )
				.on( 'woocommerce_update_variation_values', function() {
					setTimeout( function() {
						$form.find( '.variation-selector' ).each( function() {
							var $variationSelector = $( this ),
								$options = $variationSelector.find( 'select' ).find( 'option' ),
								$selected = $options.filter( ':selected' ),
								values = [];

							$options.each( function( index, option ) {
								if ( option.value !== '' ) {
									values.push( option.value );
								}
							} );

							$variationSelector.closest('.value').find( '.swatch' ).each( function() {
								var $swatch = $( this ),
									value = $swatch.attr( 'data-value' );

								if ( values.indexOf( value ) > -1 ) {
									$swatch.removeClass( 'disabled' );
								} else {
									$swatch.addClass( 'disabled' );

									if ( $selected.length && value === $selected.val() ) {
										$swatch.removeClass( 'selected' );
									}
								}
							} );
						} );
					}, 100 );
				} )
				.on( 'tawcvs_no_matching_variations', function() {
					window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
				} );
		} );
	};

	//Tracking the reset_variations button on change visibility -> change the corresponding display state
	function toggle_hidden_variation_btn() {
		const resetVariationNodes = document.getElementsByClassName('reset_variations');

		if (resetVariationNodes.length) {

			Array.prototype.forEach.call(resetVariationNodes, function (resetVariationEle) {

				let observer = new MutationObserver(function () {

					if (resetVariationEle.style.visibility !== 'hidden') {

						resetVariationEle.style.display = 'block';

					} else {

						resetVariationEle.style.display = 'none';

					}

				});

				observer.observe(resetVariationEle, {attributes: true, childList: true});

			})

		}
	}

	$( function () {
		$( '.variations_form' ).tawcvs_variation_swatches_form();
		$( document.body ).trigger( 'tawcvs_initialized' );
		toggle_hidden_variation_btn();
	} );
})( jQuery );
