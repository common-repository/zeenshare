<?php  


if($_POST['oscimp_hidden'] == 'Y') {
	//Form data sent
	$zs_enable = $_POST['zs_enable'];
	update_option('zs_enable', $zs_enable);

	$zs_domain_name = $_POST['zs_domain_name'];
	update_option('zs_domain_name', $zs_domain_name);

	$zs_domain_private_key = $_POST['zs_domain_private_key'];
	update_option('zs_domain_private_key', $zs_domain_private_key);

	
	global $wp_roles;
	foreach ( $wp_roles->role_names as $role => $name ) :
		$zs_map_role = $_POST['zs_map_'.$role];
		update_option('zs_map_'.$role, $zs_map_role);
	endforeach;
	
	
	$zs_team = new zs_team();
	$zs_team->uniqueId = $zs_domain_name;
	$zs_team->siteUrl = home_url();
	$zs_team->loginUrl = home_url().'?zs_login=1';
	$zs_team->logoutUrl = home_url().'?zs_logout=1';
	
	zs_update_team($zs_team);
?>

<div class="updated">
	<p>
		<strong><?php _e('Options saved.' ); ?> </strong>
	</p>
</div>
<?php  
} else {
	//Normal page display
	$zs_enable = get_option('zs_enable');
	$zs_domain_name = get_option('zs_domain_name');
	$zs_domain_private_key = get_option('zs_domain_private_key');
}
?>

<div class="wrap">

	<?php    echo "<h2>" . __( 'ZeenShare options', 'zs_trdom' ) . "</h2>"; ?>

	<form name="fsk_form" method="post"
		action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="oscimp_hidden" value="Y">


		<table class="form-table">
			<tr valign="top">
				<td><label for="zs_enable"><?php _e("Enable: " ); ?> </label>
				</td>
				<td><input type="checkbox" name="zs_enable" id="zs_enable"
				<?php if($zs_enable) {echo "checked=\"checked\"";}?> />
				</td>
			</tr>
			<tr valign="top">
				<td><label for="zs_domain_name"><?php _e("Domain name: " ); ?> </label>
				</td>
				<td><input class="regular-text code" type="text"
					name="zs_domain_name" id="zs_domain_name"
					value="<?php echo $zs_domain_name;?>" />
				</td>
			</tr>
			<tr valign="top">
				<td><label for="zs_domain_private_key"><?php _e("Domain private Key: " ); ?>
				</label>
				</td>
				<td><input class="regular-text code" type="text"
					name="zs_domain_private_key" id="zs_domain_private_key"
					value="<?php echo $zs_domain_private_key;?>" />
				</td>
			</tr>
			<tr valign="top">
				<td><label for="zs_domain_private_key"><?php _e("Roles mapping: " ); ?>
				</label>
				</td>
				<td>
					<table>
					<tr>
						<td><strong>Wordpress roles</strong></td>
						<td><strong>Zeenshare roles</strong></td>
					<tr>
				<?php
				
				global $wp_roles;
				
				foreach ( $wp_roles->role_names as $role => $name ) :
					$mappedRole =  get_option('zs_map_'.$role);
				    if(!$mappedRole) {
				    	if($role == 'administrator') {
				    		$mappedRole = 'TEAM_ADMIN';
				    	} else {
				    		$mappedRole = 'TEAM_CLIENT';
				    	}
				    }
					?>
					<tr>
						<td>
							<?php echo $role?>:
						</td>
						<td>
							<select name="zs_map_<?php echo $role?>">
								<option value="TEAM_ADMIN" <?php if($mappedRole == 'TEAM_ADMIN') echo 'selected="selected"'?> >Administrateur</option>
								<option value="TEAM_USER" <?php if($mappedRole == 'TEAM_USER') echo 'selected="selected"'?>>Collaborateur</option>
								<option value="TEAM_CLIENT" <?php if($mappedRole == 'TEAM_CLIENT') echo 'selected="selected"'?>>Invité</option>
							</select>
						</td>
					</tr>
					<?php
					
				endforeach;
				
				?>
				</table>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit"
				value="<?php _e('Update Options', 'zs_trdom' ) ?>" />
		</p>
	</form>
</div>
