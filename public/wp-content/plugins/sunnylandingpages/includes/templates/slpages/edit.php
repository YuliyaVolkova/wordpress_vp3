<script type="text/javascript">
	var slpages_post_type_area = true;

	<?php
		if( !$user_id )
		{
			echo 'window.location="'. admin_url( 'options-general.php?page='. $plugin_file ) .'";' ;
		}
	?>
</script>
<div class="bootstrap-wpadmin">
	<input type="hidden" name="slpages_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
	<div class="row-fluid form-horizontal">

		<div class="subsection_slpages_title control-group">
			<label class="control-label">Title</label>
			<div class="controls">
				<div class="input">
					<span class="page-title slpages_name_text_field"><?php echo $slpages_name; ?></span>
					<input type="hidden" class="input-xlarge" id="slpages_name" name="slpages_name" value="<?php echo $slpages_name; ?>"/>
				</div>
			</div>
		</div>

		<div class="subsection_slpages_url control-group">
			<label for="slpages_slug" class="control-label">Custom url</label>
			<div class="controls <?php if ($missing_slug) echo 'lp-error' ?>" id="slpages-wp-path">
				<div class="input-prepend">
					<span class="add-on"><?php echo home_url() ?>/</span><input type="textbox" class="input-xlarge" id="slpages_slug" name="slpages_slug" value="<?php echo $meta_slug ?>" />
				</div>
				<?php /*<a class="slpages-help-ico" rel="popover" data-original-title="slpages url" data-content="Pick your own url based on your Wordpress site. It will work as if the selected Sunny Landing Pages was a &quot;Page&quot; on your site.">&nbsp;</a>*/ ?>
				<?php if ($missing_slug): ?>
					<label for="slpages_slug" generated="true" class="error" style="color:#ff3300" id="lp-error-path">Valid path is required.</label>
				<?php endif; ?>
			</div>
		</div>

		<div class="control-group">
			<label for="slpages_my_selected_page" class="control-label">Sunny Landing Pages to display</label>
			<div class="controls">
				<select name="slpages_my_selected_page" id="slpages_my_selected_page" class="input-xlarge">
					<?php foreach ($field['options'] as $option): ?>
						<option <?php echo (($meta == $option['value']) ? ' selected="selected"' : '') ?> value="<?php echo $option['value'] ?>">
							<?php echo $option['label']; ?>
						</option>
					<?php endforeach; ?>
				</select>
				<a data-content="Select one of the slpages that you've created on &lt;strong&gt;https://sunnylandingpages.com/account/dashboard&lt;/strong&gt;" data-original-title="Sunny Landing Pages to be displayed" rel="popover" class="slpages-help-ico">&nbsp;</a>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Sunny Landing Pages type</label>
			<div class="controls">
				<div class="btn-group multichoice subsection" data-subsection="slpages_url" data-target="slpages-post-type">

					<select name="post-type" class="input-xlarge">
						<option value="">Normal Page</option>
						<option value="home" <?php if( $slpages_post_type == 'home' ): ?>selected=""<?php endif; ?>>Home Page</option>
						<option value="404" <?php if( $slpages_post_type == '404' ): ?>selected=""<?php endif; ?>>404 Page</option>
					</select>

				</div>

			</div>
		</div>
	</div>

	<div class="slpages-bottom-links">
		<a href="<?php echo admin_url('edit.php?post_type=slpages_post') ?>" type="submit" class="btn">Back</a> | <a href="https://sunnylandingpages.com/account/dashboard" target="_blank">Manage your Sunny Landing Pages account</a>
	</div>
</div>
