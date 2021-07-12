var frame,
	tawcvs = tawcvs || {};

jQuery( document ).ready( function ( $ ) {
	'use strict';
	var wp = window.wp,
		$body = $( 'body' );

	$( '#term-color' ).wpColorPicker();

	// Update attribute image
	$body.on( 'click', '.tawcvs-upload-image-button', function ( event ) {
		event.preventDefault();

		var $button = $( this );

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create the media frame.
		frame = wp.media.frames.downloadable_file = wp.media( {
			title   : tawcvs.i18n.mediaTitle,
			button  : {
				text: tawcvs.i18n.mediaButton
			},
			multiple: false
		} );

		// When an image is selected, run a callback.
		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$button.siblings( 'input.tawcvs-term-image' ).val( attachment.id );
			$button.siblings( '.tawcvs-remove-image-button' ).show();
			$button.parent().prev( '.tawcvs-term-image-thumbnail' ).find( 'img' ).attr( 'src', attachment.sizes.thumbnail.url );
		} );

		// Finally, open the modal.
		frame.open();

	} ).on( 'click', '.tawcvs-remove-image-button', function () {
		var $button = $( this );

		$button.siblings( 'input.tawcvs-term-image' ).val( '' );
		$button.siblings( '.tawcvs-remove-image-button' ).show();
		$button.parent().prev( '.tawcvs-term-image-thumbnail' ).find( 'img' ).attr( 'src', tawcvs.placeholder );

		return false;
	} );

	// Toggle add new attribute term modal
	var $modal = $( '#tawcvs-modal-container' ),
		$spinner = $modal.find( '.spinner' ),
		$msg = $modal.find( '.message' ),
		$metabox = null;

	$body.on( 'click', '.tawcvs_add_new_attribute', function ( e ) {
		e.preventDefault();
		var $button = $( this ),
			taxInputTemplate = wp.template( 'tawcvs-input-tax' ),
			data = {
				type: $button.data( 'type' ),
				tax : $button.closest( '.woocommerce_attribute' ).data( 'taxonomy' )
			};

		// Insert input
		$modal.find( '.tawcvs-term-swatch' ).html( $( '#tmpl-tawcvs-input-' + data.type ).html() );
		$modal.find( '.tawcvs-term-tax' ).html( taxInputTemplate( data ) );

		if ( 'color' == data.type ) {
			$modal.find( 'input.tawcvs-input-color' ).wpColorPicker();
		}

		$metabox = $button.closest( '.woocommerce_attribute.wc-metabox' );
		$modal.show();
	} ).on( 'click', '.tawcvs-modal-close, .tawcvs-modal-backdrop', function ( e ) {
		e.preventDefault();

		closeModal();
	} );

	// Send ajax request to add new attribute term
	$body.on( 'click', '.tawcvs-new-attribute-submit', function ( e ) {
		e.preventDefault();

		var $button = $( this ),
			type = $button.data( 'type' ),
			error = false,
			data = {};

		// Validate
		$modal.find( '.tawcvs-input' ).each( function () {
			var $this = $( this );

			if ( $this.attr( 'name' ) != 'slug' && !$this.val() ) {
				$this.addClass( 'error' );
				error = true;
			} else {
				$this.removeClass( 'error' );
			}

			data[$this.attr( 'name' )] = $this.val();
		} );

		if ( error ) {
			return;
		}

		// Send ajax request
		$spinner.addClass( 'is-active' );
		$msg.hide();
		wp.ajax.send( 'tawcvs_add_new_attribute', {
			data   : data,
			error  : function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'error' ).text( res ).show();
			},
			success: function ( res ) {
				$spinner.removeClass( 'is-active' );
				$msg.addClass( 'success' ).text( res.msg ).show();

				$metabox.find( 'select.attribute_values' ).append( '<option value="' + res.id + '" selected="selected">' + res.name + '</option>' );
				$metabox.find( 'select.attribute_values' ).change();

				closeModal();
			}
		} );

		
	} );

	/**
	 * Close modal
	 */
	function closeModal() {
		$modal.find( '.tawcvs-term-name input, .tawcvs-term-slug input' ).val( '' );
		$spinner.removeClass( 'is-active' );
		$msg.removeClass( 'error success' ).hide();
		$modal.hide();
	}

	// accordion js code
	
	
} );


(function($){
	// accordion js
	$('.variation-item-head').on('click', function(){
		var $clickedHead = $(this);
		$('.variation-item-head').each(function(){
			if ( $(this).is($clickedHead) ) {
				// Do nothing
			}
			else {
				$(this).removeClass('active-accordion');
				$(this).next().slideUp();
			}
		});
		$clickedHead.next().slideToggle();
		$clickedHead.toggleClass('active-accordion');
		
	});

	// accordion tab 

	$('.accor-tab-btn').on('click', function(){
		var index = $(this).index();
		$('.accor-tab-btn').removeClass('active-at-btn');
		$(this).addClass('active-at-btn');
		$('.wcvs-accor-tab-content').hide();
		$('.wcvs-accor-tab-content').eq(index).show();
	});

	$("input[name='item-styling']").change(function() {
		if(this.checked) {
			$('.vs-item-style').slideDown();
		}else{
			$('.vs-item-style').slideUp();
		}
	});

	$("input[name='item-hover']").change(function() {
		if(this.checked) {
			$('.vs-item-hover').slideDown();
		}else{
			$('.vs-item-hover').slideUp();
		}
	});

	$("input[name='item-selected']").change(function() {
		if(this.checked) {
			$('.vs-item-selected').slideDown();
		}else{
			$('.vs-item-selected').slideUp();
		}
	});

	$("input[name='item-font']").change(function() {
		if(this.checked) {
			$('.vs-item-font').slideDown();
		}else{
			$('.vs-item-font').slideUp();
		}
	});

	// Add Color Picker to all inputs that have 'color-field' class
	$(function() {
		$('.vs-color-picker').wpColorPicker();
	});
})(jQuery);


