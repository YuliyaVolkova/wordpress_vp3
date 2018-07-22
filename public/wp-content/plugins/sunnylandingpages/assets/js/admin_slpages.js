jQuery( document ).ready()
{
	

	jQuery( '#slpages_meta_box select[name="post-type"]' ).ready( function()
	{
		var disableEnter = function( e )
		{
			if( e.keyCode == 13 )
			{
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
		}

		if( jQuery( '#slpages_meta_box select[name="post-type"]' ).length )
		{
			jQuery( document ).keypress( function( e )
			{
				disableEnter( e );
			} );
			jQuery( '#slpages_slug' ).keypress( function( e )
			{
				disableEnter( e );
			} );
		}

	} );

	jQuery( 'a.slpages-hide-stats' ).ready( function()
	{
		jQuery( 'a.slpages-hide-stats' ).on( 'click', function( e )
		{
			e.preventDefault();

			if ( jQuery( 'td.column-slpages_post_stats .slpages-index-page-stats' ).is( ':visible' ) )
			{
				jQuery( this ).text( 'Show Stats' );
				jQuery( '.column-slpages_post_stats' ).width( '80px' );
			}
			else
			{
				jQuery( this ).text( 'Hide Stats' );
				jQuery( '.column-slpages_post_stats' ).width( '330px' );
			}

			jQuery( '.slpages-variation-stats-column-text' ).toggle();
			jQuery( 'td.column-slpages_post_stats .slpages-index-page-stats' ).toggle();
		});
	});

	jQuery( '#slpages_my_selected_page' ).ready( function()
	{
		var slpages_name = jQuery( '#slpages_my_selected_page' ).find( ':selected' ).text().trim();
		jQuery( '.slpages_name_text_field' ).text( slpages_name );
		jQuery( '#slpages_name' ).val( slpages_name );


		jQuery( '#slpages_my_selected_page' ).on( 'change', function()
		{
			slpages_name = jQuery( this ).find( ':selected' ).text().trim();
			jQuery( '.slpages_name_text_field' ).text( slpages_name );
			jQuery( '#slpages_name' ).val( slpages_name );
		});
	});

	jQuery( 'input#publish' ).ready( function()
	{
		jQuery( this ).click( function()
		{
			selected_value = jQuery( 'select[name="post-type"] option:selected' ).val();

			if( selected_value == '404' || selected_value == 'home' )
			{
				jQuery( '#slpages_slug' ).val( '' );
			}

			return true;
		} );
	} );

	jQuery( '#slpages_meta_box select[name="post-type"]' ).ready( function()
	{
		if ( jQuery( '#slpages_meta_box select[name="post-type"]' ).val() === 'home' || jQuery( '#slpages_meta_box select[name="post-type"]' ).val() === '404' )
		{
			jQuery( '.subsection_slpages_url' ).addClass( 'hidden' );
		}
		else
		{
			jQuery( '.subsection_slpages_url' ).removeClass( 'hidden' );
			resizeslpagesSlug();
		}

		jQuery( '#slpages_meta_box select[name="post-type"]' ).on( 'change', function()
		{
			if ( jQuery( this ).val() === 'home' || jQuery( this ).val() === '404' )
			{
				jQuery( '.subsection_slpages_url' ).addClass( 'hidden' );
			}
			else
			{
				jQuery( '.subsection_slpages_url' ).removeClass( 'hidden' );
				resizeslpagesSlug();
			}
		});
	});

	jQuery( '#slpages-wp-path' ).ready( function()
	{
		setTimeout( function()
		{

			var submit_valid = false;

			resizeslpagesSlug();

			var add_on_width = parseInt( jQuery( '#slpages-wp-path .add-on' ).outerWidth(), 10 ) + 6;
			var submit_valid = false;
			jQuery( '#slpages_slug' ).css( 'padding-left', add_on_width + 'px' );

			jQuery( '#publish' ).click( function( e )
			{
				if ( !submit_valid )
				{
					e.preventDefault();

					jQuery( '#slpages_slug' ).css( 'border', '0' );

					if ( ( jQuery( '#slpages_slug' ).val() === '' && jQuery( 'select[name="post-type"]' ).val() === '' ) )
					{
						if ( jQuery( '#slpages_slug' ).val() === '' && jQuery( 'select[name="post-type"]' ).val() === '' )
						{
							jQuery( '#slpages_slug' ).css( 'border', '1px solid red' );
						}
					}
					else
					{
						submit_valid = true;
						jQuery( this ).trigger( 'click' );
					}
				}
			});
		}, 500 );
	});

	function resizeslpagesSlug()
	{
		setTimeout( function()
		{
			var add_on_width = parseInt( jQuery( '#slpages-wp-path .add-on' ).outerWidth(), 10 ) + 6;
			jQuery( '#slpages_slug' ).css( 'padding-left', add_on_width + 'px' );
		}, 500 );
	}
}

function slpages_redirection( location )
{
	jQuery( 'body' ).html( '' );
	window.location = location;
}

function slpages_remove_edit()
{
	jQuery( '#poststuff' ).hide();
}
