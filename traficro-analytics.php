<?php
/*
Plugin Name: Trafic.ro Analytics
Plugin URI: http://www.slickdesign.ro/wordpress-plugins/plugin-wordpress-pentru-adaugare-cod-trafic-ro-in-site/
Description: Afiseaza codul de monitorizare trafic.ro pe toate paginile publice.
Version: 1.2
Author: Mihai Marica
Author URI: http://www.slickdesign.ro
License: GPLv2
*/

$traficro_plugin_options = null;

// load localization file
load_plugin_textdomain('traficro', false, dirname(plugin_basename( __FILE__ )) . "/languages/" ); 

// Add a menu for our option page
add_action('admin_menu', 'traficro_plugin_add_page');

/**
 * Add the plugin entry in the admin menu
 * 
 * @return void
 */
function traficro_plugin_add_page() {
    $page_id = add_options_page( __('Trafic.ro Analytics', 'traficro'), __('Trafic.ro Analytics', 'traficro'), 'manage_options', 'traficro_myplugin', 'traficro_plugin_option_page' );

    // enqueue css files only to this plugin's admin page
    add_action('admin_print_styles-' . $page_id, 'traficro_plugin_css_files');
}

/**
 * Add the CSS files to the admin page (only when this plugin page is displayed)
 * 
 * @return void
 */
function traficro_plugin_css_files() {
    wp_enqueue_style('traficro_plugin_css', plugins_url('traficro-analytics.css', __FILE__));
}

/**
 * Show a notice in the admin interface if the tracking code has not been set
 * 
 * @return void
 */
function traficro_missing_tracking_code_notice(){
    echo "<div>".sprintf(__("Your tracking code is missing. Please fill in the coresponding field in the %sTrafic.ro Analytics settings page%s.", 'traficro'), "<a href='options-general.php?page=traficro_myplugin'>", "</a>")."</div>";
}

/**
 * Load the plugin options from Wordpress
 * 
 * @global array $traficro_plugin_options Plugin options as loaded from Wordpress
 * @param string $option_name The option identifier
 * @return array
 */
function traficro_option($option_name) {
    global $traficro_plugin_options;

    // if options arean't already loaded, do it now
    if ($traficro_plugin_options === null) {
        // get plugin options from Wordpress
        $initial = get_option( 'traficro_plugin_options' );
        
        // complete the missing pieces with default for compatibility with older versions
        $traficro_plugin_options = array(
            'tracking_code' => isset($initial['tracking_code']) ? $initial['tracking_code'] : '',
            'footer_output' => isset($initial['footer_output']) ? $initial['footer_output'] : false,
            'footer_output_before' => isset($initial['footer_output_before']) ? $initial['footer_output_before'] : "<div style='text-align: center'>",
            'footer_output_after' => isset($initial['footer_output_after']) ? $initial['footer_output_after'] : "</div>",
        );
        
        // if the tracking code is not set, show the notice
        if(!strlen($traficro_plugin_options['tracking_code'])) {
            add_action( 'admin_notices', 'traficro_missing_tracking_code_notice' );
        }
    }

    return $traficro_plugin_options[$option_name];
}

/**
 * Generate the options page
 * 
 * @return void
 */ 
function traficro_plugin_option_page() {
?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2>Trafic.ro Analytics</h2>
        <form action="options.php" method="post">
            <?php settings_fields( 'traficro_plugin_options' ); ?>
            <?php do_settings_sections( 'traficro_myplugin' ); ?>
            <br/>
            <input name="Submit" type="submit" value="<?php _e("Save Changes", 'traficro'); ?>" />
        </form>
    </div>
<?php
}

add_action( 'admin_init', 'traficro_plugin_admin_init' );

/**
 * Register and define the settings
 * 
 * @return void
 */
function traficro_plugin_admin_init(){
	register_setting(
		'traficro_plugin_options',
		'traficro_plugin_options'
	);
	add_settings_section(
		'traficro_plugin_main',
		__('How can I use this plugin?!', 'traficro'),
		'traficro_plugin_section_id',
		'traficro_myplugin'
	);
	add_settings_field(
		'traficro_plugin_tracking_code',
		__('Trafic.ro tracking code', 'traficro'),
		'traficro_plugin_setting_input_tracking_code',
		'traficro_myplugin',
		'traficro_plugin_main'
	);
	add_settings_field(
		'traficro_plugin_footer_output',
		__('Output tracking code to footer', 'traficro'),
		'traficro_plugin_setting_input_footer_output',
		'traficro_myplugin',
		'traficro_plugin_main',
                array('label_for'=>'footer_output')
	);
	add_settings_field(
		'traficro_plugin_footer_output_before',
		__('Code to output to footer before the tracking code', 'traficro'),
		'traficro_plugin_setting_input_footer_output_before',
		'traficro_myplugin',
		'traficro_plugin_main'
	);
	add_settings_field(
		'traficro_plugin_footer_output_after',
		__('Code to output to footer after the tracking code', 'traficro'),
		'traficro_plugin_setting_input_footer_output_after',
		'traficro_myplugin',
		'traficro_plugin_main'
	);
}

/**
 * The plugin section
 *
 * @return void 
 */
function traficro_plugin_section_id() {
        echo "<p class='traficro_attention'>".__("Please don't forget to fill in the 'Trafic.ro tracking code' field, regardless of the method you choose for using this plugin.", 'traficro')."</p>";
        printf(__("%sThe easiest way%s", 'traficro'), "<h4>1. ", "</h4>");
        echo "<p>".__("Suitable for most people, using this method requires you to just check 'Output tracking code to footer'. The plugin will automatically output the code to the footer of the page (just before the closing of the html tag). If you want, you can alter the 'before' and 'after' values to change positioning in the page (default is centered).", 'traficro')."</p>";
        
        printf(__("%sThe recommended way%s", 'traficro'), "<h4>2. ", "</h4>");
        printf(__("%sYou must insert a function into a theme file (typically footer.php). To do that, paste the code below to a position of convenience in the file.%s %s", 'traficro'),
            "<p>", "</p>", "<span class='traficro_code_block'>&lt;?php if (function_exists('traficro_tracking_code')) echo traficro_tracking_code(); ?&gt;</span><br/>"
        );
        
        printf(__("%sThe widget way%s", 'traficro'), "<h4>3. ", "</h4>");
        echo "<p>".__("Also very easy, this method will probably not be used at all, but I wanted to include it anyway, just in case. You can add the tracking code to your site by using the 'Trafic.ro Tracking Code' widget in the Appeareance => Widgets page. The reason I included this is that some themes also allow widgets in the footer of pages.", 'traficro')."</p>";
        echo "<h4>".__("Plugin settings", 'traficro')."</h4>";
}

/**
 * Display the tracking cookie field
 * 
 * @return void
 */
function traficro_plugin_setting_input_tracking_code() {
	// get option 'tracking_code' value from the database
	$tracking_code = traficro_option('tracking_code');
	// echo the field
	echo "<textarea cols='120' rows='22' id='tracking_code' name='traficro_plugin_options[tracking_code]'>".htmlentities($tracking_code, ENT_QUOTES)."</textarea>";
}

/**
 * Display the footer section
 * 
 * @return void
 */
function traficro_plugin_setting_input_footer_output() {
	// get option 'footer_output' value from the database
	$footer_output = traficro_option('footer_output');
	// echo the field
	echo "<input id='footer_output' name='traficro_plugin_options[footer_output]' type='checkbox'".($footer_output ? " checked='checked'" : '')." />";
}

/**
 * Display the footer before field
 * 
 * @return void
 */
function traficro_plugin_setting_input_footer_output_before() {
	// get option 'footer_output_before' value from the database
	$footer_output_before = traficro_option('footer_output_before');
	// echo the field
	echo "<input type='text' size='60' id='footer_output_before' name='traficro_plugin_options[footer_output_before]' value='".htmlentities($footer_output_before, ENT_QUOTES)."' />";
}

/**
 * Display the footer after field
 * 
 * @return void
 */
function traficro_plugin_setting_input_footer_output_after() {
	// get option 'footer_output_after' value from the database
	$footer_output_after = traficro_option('footer_output_after');
	// echo the field
	echo "<input type='text' size='60' id='footer_output_after' name='traficro_plugin_options[footer_output_after]' value='".htmlentities($footer_output_after, ENT_QUOTES)."' />";
}

// Echo the tracking code in the footer if that option is set
$footer_output = traficro_option('footer_output');
if (isset($footer_output) && $footer_output) {
    add_action( 'wp_footer', 'traficro_analytics_footer_output' );
    
    /**
     * Output the tracking code in the footer
     * 
     * @return void
     */
    function traficro_analytics_footer_output() {
        echo traficro_option('footer_output_before') . traficro_tracking_code() . traficro_option('footer_output_after');
    }
}

/**
 * Return the tracking code
 * 
 * @param array $atts Attributes
 * @return string
 */
function traficro_tracking_code($atts = null) {
        return traficro_option('tracking_code');
}

// add shortcode functionality
add_shortcode('traficro', 'traficro_tracking_code');  

/**
 * Add widget functionality
 * 
 * @param type $args
 */
function traficro_widget($args) {
    echo $args['before_widget'];
    echo traficro_option('tracking_code');
    echo $args['after_widget'];
}
 
/**
 * Display the plugin in the widgets list
 * 
 */
function traficro_widget_init()
{
  register_sidebar_widget(__('Trafic.ro Tracking Code', 'traficro'), 'traficro_widget');
}

add_action("plugins_loaded", "traficro_widget_init");
