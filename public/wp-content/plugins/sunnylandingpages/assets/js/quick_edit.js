(function($) {
	// we create a copy of the WP inline edit post function
   var $wp_inline_edit = inlineEditPost.edit;
   // and then we overwrite the function with our own code
   inlineEditPost.edit = function( id ) {

      // "call" the original WP edit function
      // we don't want to leave WordPress hanging
      $wp_inline_edit.apply( this, arguments );

      // now we take care of our business

      // get the post ID
      var $post_id = 0;
      if ( typeof( id ) == 'object' )
         $post_id = parseInt( this.getId( id ) );

      if ( $post_id > 0 ) {

         // define the edit row
         var $edit_row = $( '#edit-' + $post_id );
		
		$edit_row.find("select[name=_status]").find("option[value=pending]").remove();
        
		// get the release date
		var $slpages_slug = $( '#slpages_slug-' + $post_id ).text();

		 // populate the release date
		 $edit_row.find( 'input[name="slpages_slug"]' ).val( $slpages_slug );
		 $edit_row.find( 'input[name="slpages_slug_hidden"]' ).val( $slpages_slug );
		 
		 // get the release date
		var $post_type = $( '#slpages_post_type-' + $post_id ).text();
		
		$post_type = $post_type.toLowerCase();
		 // populate the release date
		 $edit_row.find( 'select[name="post-type"]' ).val( $post_type  );
		 
 
		if($post_type == 'home' || $post_type == '404'){
				$edit_row.find("input[name=slpages_slug]").addClass('hidden'); 
				$edit_row.find("input[name=slpages_slug_hidden]").removeClass('hidden'); 
		}
		else{
			$edit_row.find("input[name=slpages_slug]").removeClass('hidden'); 
			$edit_row.find("input[name=slpages_slug_hidden]").addClass('hidden'); 
		}
		
		if($post_type == '404'){
			$edit_row.find("input[name=slpages_slug]").val('');
		}		
		  // get the release date
		var $post_status = $( '#slpages_post_status-' + $post_id ).text();

		 // populate the release date
		 $edit_row.find( 'select[name="_status"]' ).val( $post_status.toLowerCase() );
		 
		  // get the release date
		var $slpages_name = $( '#slpages_name-' + $post_id ).text();

		 // populate the release date
		 $edit_row.find( 'input[name="slpages_name"]' ).val( $slpages_name );
		 $edit_row.find( 'span[data-name="slpages_name"]' ).html( $slpages_name );
		 
		 var $slpages_my_selected_page = $( '#slpages_my_selected_page-' + $post_id ).text();

		 // populate the release date
		 $edit_row.find( 'input[name="slpages_my_selected_page"]' ).val( $slpages_my_selected_page );
		
		
		
		$edit_row.find("select[name=post-type]").change(function(){
				if($(this).val() == 'home' || $(this).val() == '404'){
						$edit_row.find("input[name=slpages_slug]").addClass('hidden'); 
						$edit_row.find("input[name=slpages_slug_hidden]").removeClass('hidden'); 
				}
				else{
					$edit_row.find("input[name=slpages_slug]").removeClass('hidden'); 
					$edit_row.find("input[name=slpages_slug_hidden]").addClass('hidden'); 
				}
		});
		
		var $get_row = $( '#post-' + $post_id );
		
		// var delete_href = $get_row.find(".submitdelete").attr("href");
		
		// $edit_row.find(".submitdelete").attr("href");
		// $edit_row.find(".submit.inline-edit-save").append("<a type='button' href='"+delete_href+"' class='button delete slpages-button-danger slp-delete-confirmation'>Delete</a>");
		
		// $('.slp-delete-confirmation').on('click', function () {
			// return confirm('Please delete the landing page from your Sunny Landing Pages application\'s dashboard.');
		// });
		  
	  }

   };
  
})(jQuery);