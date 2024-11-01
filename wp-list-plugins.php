<?php
/**
 * Plugin Name: WP List PlugIns
 * Plugin URI: http://blog.ppfeufer.de/wordpress-wp-list-plugins-ein-plugin-zum-auflisten-der-installierten-wordpress-plugins/
 * Description: Shows all installed plugins with a signle shortcode. <code>&#91;pluginlist&#93;</code> to show all, <code>&#91;pluginlist show="active"&#93;</code> to show only activated plugins and <code>&#91;pluginlist show="inactive"&#93;</code> to show all inactive plugins.
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 * Version: 2.2
 */

define('WPLISTPLUGINS_VERSION', '2.2');


/**
 * CSS in Wordpress einbinden
 */
if(!is_admin()) {
	$css_url = plugins_url(basename(dirname(__FILE__)) . '/css/wp-list-plugins.css');

	wp_register_style('wp-list-plugins', $css_url, array(), WPLISTPLUGINS_VERSION, 'screen');
	wp_enqueue_style('wp-list-plugins');
}

if(!function_exists('get_plugins')) {
	require_once (ABSPATH . 'wp-admin/includes/plugin.php');
}

/**
 * Shortcode zu Wordpress hinzufügen
 */
add_shortcode('pluginlist', 'sc_pluginliste');

/**
 * Shortcode in HTML-Code umwandeln
 * @param $atts
 */
function sc_pluginliste($atts) {
	extract(shortcode_atts(array(
		"show" => ''
	), $atts));

	$array_Plugins = get_plugins();

	if($show == '') {
		$show = 'all';
	}

	if(empty($array_Plugins)) {
		return '<p>' . __('Couldn&#8217;t open plugins directory or there are no plugins available.', 'wp-list-plugins') . '</p>';
	}

	if(!(is_single() || is_page())) {
		return '<p>' . __('Pluginlist will only be shown in article, not in overview ...', 'wp-list-plugins') . '</p>';
	}

	return get_my_pluginlist($array_Plugins, $show);
}

function get_my_pluginlist($array_Plugins, $var_sShow) {
	$var_iPlugInNumber = 1;

	$var_translatePlugin = __('Plugin', 'wp-list-plugins');
	$var_translateVersion = __('Version', 'wp-list-plugins');
	$var_translateDescription = __('Description', 'wp-list-plugins');

	$plugins_allowedtags1 = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'abbr' => array(
			'title' => array()
		),
		'acronym' => array(
			'title' => array()
		),
		'code' => array(),
		'em' => array(),
		'strong' => array()
	);
	$plugins_allowedtags2 = array(
		'abbr' => array(
			'title' => array()
		),
		'acronym' => array(
			'title' => array()
		),
		'code' => array(),
		'em' => array(),
		'strong' => array()
	);

	switch($var_sShow) {
		case 'all':
			$var_sHeadline = __('List of all installed plugins. Inactive plugins will be stroke through.', 'wp-list-plugins');
			break;

		case 'active':
			$var_sHeadline = __('List of all installed (active) plugins.', 'wp-list-plugins');
			break;

		case 'inactive':
			$var_sHeadline = __('List of all installed (inactive) plugins.', 'wp-list-plugins');
			break;
	}

	$var_sHtml = '
			<div class="plugInListWrapper">
				<div class="plugInListLine plugInListHeadline">
					<div class="plugInListHeadDescription">' . $var_sHeadline . '</div>
					<div class="plugInListNumber">Nr.</div>
					<div class="plugInListName">' . __('Plugin', 'wp-list-plugins') . '</div>
					<div class="plugInListVersion">' . __('Version', 'wp-list-plugins') . '</div>
					<div class="plugInListDescription">' . __('Description', 'wp-list-plugins') . '</div>
				</div>';

	foreach($array_Plugins as $plugin_file => $plugin_data) {
		if(is_plugin_active($plugin_file)) {
			if($var_sShow == 'inactive') {
				continue;
			}

			if($var_sShow == 'all') {
				$plugin_data['active'] = 'plugInIsActive';
			}
		} else {
			if($var_sShow == 'active') {
				continue;
			}

			if($var_sShow == 'all') {
				$plugin_data['active'] = 'plugInIsInactive';
			}
		}

		// PlugIn-Daten sammeln
		$plugin_data['Title'] = wp_kses($plugin_data['Title'], $plugins_allowedtags1);
		$plugin_data['Title'] = ($plugin_data['PluginURI']) ? '<a href="' . $plugin_data['PluginURI'] . '">' . $plugin_data['Title'] . '</a>' : $plugin_data['Title'];
		$plugin_data['Version'] = wp_kses($plugin_data['Version'], $plugins_allowedtags1);
		$plugin_data['Description'] = wp_kses($plugin_data['Description'], $plugins_allowedtags2);
		$plugin_data['Author'] = wp_kses($plugin_data['Author'], $plugins_allowedtags1);
		$plugin_data['Author'] = (empty($plugin_data['Author'])) ? '' : ' <cite>' . sprintf(__('By %s', 'wp-list-plugins'), ($plugin_data['AuthorURI']) ? '<a href="' . $plugin_data['AuthorURI'] . '">' . $plugin_data['Author'] . '</a>' : $plugin_data['Author']) . '.</cite>';

		$var_sHtml .= '
				<div class="plugInListLine ' . $plugin_data['active'] . '">
					<div class="plugInListNumber">
						<p>' . $var_iPlugInNumber . '</p>
					</div>
					<div class="plugInListName">
						<p>' . $plugin_data['Title'] . '</p>
					</div>
					<div class="plugInListVersion">
						<p>' . $plugin_data['Version'] . '</p>
					</div>
					<div class="plugInListDescription">
						<p>' . $plugin_data['Description'] . '</p>
						<p>' . $plugin_data['Author'] . '</p>
					</div>
				</div>';

		$var_iPlugInNumber++;
	}

	$var_sHtml .= '</div>';

	return $var_sHtml;
}

/**
 * Sprachdatei wählen
 */
if(function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('wp-list-plugins', false, dirname(plugin_basename(__FILE__)) . '/l10n/');
}
?>