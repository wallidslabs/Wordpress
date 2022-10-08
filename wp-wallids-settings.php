<?php
class WallidsSecuritySettings
{
    private $__wallids_security_settings_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'wallids_security_settings_add_plugin_page'));
        add_action('admin_init', array($this, 'wallids_security_settings_page_init'));
    }

    public function wallids_security_settings_add_plugin_page()
    {
        add_menu_page(
            'Wallids Security Settings', // page_title
            'Wallids Security Settings', // menu_title
            'manage_options', // capability
            'wallids-security-settings', // menu_slug
            array($this, 'wallids_security_settings_create_admin_page'), // function
            'https://cdn-wallids.s3.eu-central-1.amazonaws.com/images/faviconwall.svg', // icon_url
            1// position
        );
    }

    public function wallids_security_settings_create_admin_page()
    {
        $this->wallids_security_settings_options = get_option('wallids_security_settings_option_name'); ?>

		<div class="wrap">
			<h2>Wallids Security Settings</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
                    settings_fields('wallids_security_settings_option_group');
                    do_settings_sections('wallids-security-settings-admin');
                    submit_button();
                ?>
			</form>
		</div>
	<?php }

    public function wallids_security_settings_page_init()
    {
        register_setting(
            'wallids_security_settings_option_group', // option_group
            'wallids_security_settings_option_name', // option_name
            array($this, 'wallids_security_settings_sanitize') // sanitize_callback
        );

        add_settings_section(
            'wallids_security_settings_setting_section', // id
            'Settings', // title
            array($this, 'wallids_security_settings_section_info'), // callback
            'wallids-security-settings-admin' // page
        );

        add_settings_field(
            'secret_key_0', // id
            'Secret Key:', // title
            array($this, 'secret_key_0_callback'), // callback
            'wallids-security-settings-admin', // page
            'wallids_security_settings_setting_section' // section
        );

        add_settings_field(
            'monitoring_1', // id
            'Monitoring:', // title
            array($this, 'monitoring_1_callback'), // callback
            'wallids-security-settings-admin', // page
            'wallids_security_settings_setting_section' // section
        );
    }

    public function wallids_security_settings_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['secret_key_0'])) {
            $sanitary_values['secret_key_0'] = sanitize_text_field($input['secret_key_0']);
        }

        if (isset($input['monitoring_1'])) {
            $sanitary_values['monitoring_1'] = $input['monitoring_1'];
        }

        return $sanitary_values;
    }

    public function wallids_security_settings_section_info()
    {

    }

    public function secret_key_0_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="wallids_security_settings_option_name[secret_key_0]" id="secret_key_0" value="%s">',
            isset($this->wallids_security_settings_options['secret_key_0']) ? esc_attr($this->wallids_security_settings_options['secret_key_0']) : ''
        );
    }

    public function monitoring_1_callback()
    {
        ?> <fieldset><?php $checked = (isset($this->wallids_security_settings_options['monitoring_1']) && $this->wallids_security_settings_options['monitoring_1'] === 'on') ? 'checked' : ''; ?>
		<label for="monitoring_1-0"><input type="radio" name="wallids_security_settings_option_name[monitoring_1]" id="monitoring_1-0" value="on" <?php echo $checked; ?>> ON</label><br>
		<?php $checked = (isset($this->wallids_security_settings_options['monitoring_1']) && $this->wallids_security_settings_options['monitoring_1'] === 'off') ? 'checked' : ''; ?>
		<label for="monitoring_1-1"><input type="radio" name="wallids_security_settings_option_name[monitoring_1]" id="monitoring_1-1" value="off" <?php echo $checked; ?>> OFF</label></fieldset> <?php
}

}
if (is_admin()) {
    $wallids_security_settings = new WallidsSecuritySettings();
}
