<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/admin/partials
 * @author     Smarty Studio | Martin Nestorov
 */
?>

<div class="wrap">
	<h1><?= esc_html(get_admin_page_title()); ?></h1>
	<form action="options.php" method="post">
		<?php settings_fields('smarty-sameday-settings-options'); ?>
	
		<div class="smarty-sameday-settings-section">
			<?php do_settings_sections('smarty-sameday-settings'); ?>
		</div>
	
		<?php submit_button(); ?>
	</form>
</div>