<div class="wrap">
	<h2>bSocial Options</h2>
	<form method="post" action="options.php">
		<?php settings_fields('bsocial-options'); ?>
		<table class="form-table">
			<tr valign="top"><th scope="row">Add Open Graph metadata to pages</th>
				<td><input name="bsocial-options[open-graph]" type="checkbox" value="1" <?php checked( '1' , $options['open-graph']); ?> /></td>
			</tr>

			<tr valign="top"><th scope="row">Activate featured comments</th>
				<td><input name="bsocial-options[featured-comments]" type="checkbox" value="1" <?php checked( '1' , $options['featured-comments']); ?> /></td>
			</tr>

			<tr valign="top"><th scope="row">Activate Twitter components</th>
				<td><input name="bsocial-options[twitter-api]" type="checkbox" value="1" <?php checked( '1' , $options['twitter-api']); ?> /></td>
			</tr>

			<!-- twitter details -->
			<tr valign="top"><th scope="row">Twitter application consumer key</th>
				<td><input type="text" name="bsocial-options[twitter-consumer-key]" value="<?php echo esc_attr( $options['twitter-consumer-key'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Twitter application consumer secret</th>
				<td><input type="text" name="bsocial-options[twitter-consumer-secret]" value="<?php echo esc_attr( $options['twitter-consumer-secret'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Twitter application access token</th>
				<td><input type="text" name="bsocial-options[twitter-access-token]" value="<?php echo esc_attr( $options['twitter-access-token'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Twitter application access secret</th>
				<td><input type="text" name="bsocial-options[twitter-access-secret]" value="<?php echo esc_attr( $options['twitter-access-secret'] ); ?>" /></td>
			</tr>

			<tr valign="top"><th scope="row">Twitter @username for site</th>
				<td><input type="text" name="bsocial-options[twitter-card_site]" value="<?php echo esc_attr( $options['twitter-card_site'] ); ?>" /></td>
			</tr>

			<tr valign="top"><th scope="row">Ingest tweets that link to this site as comments on the post they link to</th>
				<td><input name="bsocial-options[twitter-comments]" type="checkbox" value="1" <?php checked( '1' , $options['twitter-comments']); ?> /></td>
			</tr>

			<tr valign="top"><th scope="row">Activate Facebook components</th>
				<td><input name="bsocial-options[facebook-api]" type="checkbox" value="1" <?php checked( '1' , $options['facebook-api']); ?> /></td>
			</tr>

			<tr valign="top"><th scope="row">Add a Facebook like button to every post</th>
				<td><input name="bsocial-options[facebook-add_button]" type="checkbox" value="1" <?php checked( '1' , $options['facebook-add_button']); ?> /></td>
			</tr>

			<tr valign="top"><th scope="row">Facebook admin IDs</th>
				<td><input type="text" name="bsocial-options[facebook-admins]" value="<?php echo esc_attr( $options['facebook-admins'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Facebook app ID/API key</th>
				<td><input type="text" name="bsocial-options[facebook-app_id]" value="<?php echo esc_attr( $options['facebook-app_id'] ); ?>" /></td>
			</tr>
			<tr valign="top"><th scope="row">Facebook secret</th>
				<td><input type="text" name="bsocial-options[facebook-secret]" value="<?php echo esc_attr( $options['facebook-secret'] ); ?>" /></td>
			</tr>

			<tr valign="top"><th scope="row">Ingest Facebook comments</th>
				<td><input name="bsocial-options[facebook-comments]" type="checkbox" value="1" <?php checked( '1' , $options['facebook-comments']); ?> /></td>
			</tr>
		</table>
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
