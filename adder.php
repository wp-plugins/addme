<?php
/*
Plugin Name: Add me
Plugin URI: https://www.facebook.com/wp.addme
Description: Add strings, call to action or blocks of script after, before and in many place in your contents.
Text Domain: addme
Domain Path: /
Author: Philippe Gras
Version: 0.1
Author URI: http://www.avoirun.com/
*/

load_plugin_textdomain('addme', false, dirname( plugin_basename( __FILE__ ) ) );

class ContentAdder {
	var $init = 0;

	function ContentAdder() {
		register_activation_hook( __FILE__, array( $this, 'addme_in_content' ) );
		register_deactivation_hook( __FILE__, array( $this, 'add_flush_options' ) );

		global $wp_filter;
		/********************************
		 * Checking if there are already
		 * filters  from other plugins
		 * with 10 for priority
		 */
		$count = count($wp_filter[the_content][10]);

		/********************************
		 * If it is, remove it from array
		 * and put them in a specific one
		 */
		if ($count > 6) {
			$spare_filters = array_chunk($wp_filter[the_content][10], 6, true);
			array_splice($wp_filter[the_content][10], 6, $count);
		}

		/********************************
		 * Push the plugin options in the
		 * $wp_filter array with priority
		 * 10 and other priorities
		 */
		for ($i = 0; $i < count($this->add_build_array()); $i++) {
			$transit = $this->add_build_array();
			$wp_filter[the_content][intval($transit[$i][0])]['addme_in_content_'.$i] = array('function' => array($this, 'addme_in_content'), 'accepted_args' => 1);
		}

		/********************************
		 * Replace the removed filters at
		 * the end of the filters array
		 */
		if ($count > 6) {
			for ($i = 1; $i < count($spare_filters); $i++) {
				foreach ( $spare_filters[$i] as $key => $value ){
					$wp_filter[the_content][10][$key] = $spare_filters[1][$key];
				}
			}
		}

		add_action('admin_menu', array( $this, 'add_init' ) );
		add_action( 'admin_init', array( $this, 'add_settings_register' ) );
	}

	function add_init() {
		add_options_page( 'Add your stuff in content here!', 'Add Me', 'manage_options', 'adder', array( $this, 'add_admin' ) );
	}

	/********************************
	 * Delete all the plugin options
	 * when the plugin is disabled :
	 */
	function add_flush_options() {
		for ($i = $this->add_last_option(); $i > 0; $i--) {
			delete_option( 'add_' . $i . '__preced' );
			delete_option( 'add_' . $i . '__string' );
			delete_option( 'add_' . $i . '__option' );
			delete_option( 'add_' . $i . '__exclue' );
			unregister_setting( 'add_options_group', 'add_' . $i . '__option', 'add_sanz_affich' );
			unregister_setting( 'add_options_group', 'add_' . $i . '__preced', 'add_sanz_preced' );
			unregister_setting( 'add_options_group', 'add_' . $i . '__string', 'add_sanz_zonetx' );
			unregister_setting( 'add_options_group', 'add_' . $i . '__exclue', 'add_sanz_exclue' );
			remove_action( 'admin_init', 'add_settings_register' );
		}
	}

	/********************************
	 * terms
	 */
	function add_get_tax_array() {
		$taxes = array( 
				'category',
			//	'post_tag',
				);
		$args = array(
				'orderby'           => 'name', 
				'order'             => 'ASC',
				'hide_empty'        => true, 
				'exclude'           => array(), 
				'exclude_tree'      => array(), 
				'include'           => array(),
				'number'            => '', 
				'fields'            => 'all', 
				'slug'              => '', 
				'parent'            => '',
				'hierarchical'      => true, 
				'child_of'          => 0, 
				'get'               => '', 
				'name__like'        => '',
				'description__like' => '',
				'pad_counts'        => false, 
				'offset'            => '', 
				'search'            => '', 
				'cache_domain'      => 'core'
				);
			$terms = get_terms($taxes, $args);
		if (!empty($terms) && !is_wp_error( $terms)) {
			return $terms;
		} else	return array();
	}

	/********************************
	 * Get locale for localization :
	 */
	function add_locale_btn() {
		$locale = get_locale();
		switch($locale) {
			case 'fr_FR':
				$btn_donate = array('48QJ88SAPH4XQ', 'fr_FR/FR', 'PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !');
			break;
			case 'en_US':
				$btn_donate = array('A9JAJEMNV4CDG', 'en_US', 'PayPal — The safer, easier way to pay online.');
			break;
			case 'de_DE':
				$btn_donate = array('5UZQMCXX2G38E', 'de_DE/DE', 'Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.');
			break;
			case 'es_ES':
				$btn_donate = array('HTLPPRTJA7T3W', 'es_ES/ES', 'PayPal. La forma rápida y segura de pagar en Internet.');
			break;
			default:
				$btn_donate = array('WMYBQB4ZH7HDW', 'fr_XC', 'PayPal — The safer, easier way to pay online.');
			break;
		}
		return $btn_donate;
	}
	/********************************
	 * ending for loop
	 */
	function add_keys_filter($key) {
		return preg_match('#add_[\d]+__[\w]+#', $key);
	}

	function add_last_option() {
		$all_options = wp_load_alloptions();
		$allkeys = array_keys($all_options);
		$filters = array_filter($allkeys, array($this, 'add_keys_filter'));
		$reduced = count($filters) / 4;
		return $reduced + 1;
	}

	/********************************
	 * Return each option
	 */
	function add_is_priority($i) {
		$option = get_option('add_' . $i . '__preced', 10);
		return $option;
	}

	function add_the_string($i) {
		$nostring = sprintf(__( 'Add your extra content #%d or block of scripts here, define above where it must be displayed against the content and the order of its display compared to the other. Priority is usually set to 10. If you enter a little number, you may see strange things in the post content, but you can defer your display easily by selecting greater numbers. Have fun!', 'addme'), $i);
		$option = get_option('add_' . $i . '__string', $nostring);
		$option = stripslashes(htmlspecialchars_decode($option));
		return $option;
	}

	function add_where_todo($i) {
		$option = get_option('add_' . $i . '__option', 'none');
		return $option;
	}

	function add_who_exclus($i) {
		$option = get_option('add_' . $i . '__exclue', array());
		return $option;
	}

	/********************************
	 * Registring all options
	 */
	function add_settings_register() {
		for ($i = 1; $i <= $this->add_last_option(); $i++) {
			register_setting('add_' . $i . '__options_group', 'add_' . $i . '__option', array($this, 'add_sanz_affich'));
			register_setting('add_' . $i . '__options_group', 'add_' . $i . '__preced', array($this, 'add_sanz_preced'));
			register_setting('add_' . $i . '__options_group', 'add_' . $i . '__string', array($this, 'add_sanz_zonetx'));
			register_setting('add_' . $i . '__options_group', 'add_' . $i . '__exclue', array($this, 'add_sanz_exclue'));
		}
	}


	/********************************
	 * Sanitize each option
	 */
	function add_sanz_affich($option) {
		return $option;
	}

	function add_sanz_preced($option) {
		$output = (int)$option > 0 ? $option : 10;
		return $output;
	}

	function add_sanz_zonetx($option) {
		return esc_textarea($option);
	}

	function add_sanz_exclue($option) {
		return (array)$option;
	}

	/********************************
	 * Wrap the content
	 */

	function add_build_array() {
		$array = array();
		for ($i = 1; $i <= $this->add_last_option()-1; $i++) {
			$array[$this->add_is_priority($i) . $i] = array($this->add_is_priority($i), $this->add_the_string($i), $this->add_where_todo($i), $this->add_who_exclus($i));
		}

		ksort($array);
		return array_values($array);
	}

	function addme_in_content($content) {
		$i = $this->init++;
		$cat = get_the_category($post->ID);
		$tr_ver = $this->add_build_array();
		$string = $tr_ver[$i][1];
		$option = $tr_ver[$i][2];
		$exclus = $tr_ver[$i][3];
		if (is_single()) {
			if (!in_array($cat[0]->term_id, $exclus)) {
				$string = html_entity_decode(htmlspecialchars_decode($string), ENT_QUOTES);
				$ct_arr = explode('</p>', $content);
				$count = count($ct_arr);

				switch($option) {
				case 'above_c':
					$content = $string . $content;
				break;
				case 'under_c':
					$content = $content . $string;
				break;
				case 'twice_c':
					$content = $string . $content . $string;
				break;
				case 'above_p':
					$content = implode('</p>', array_slice($ct_arr, 0, $count-2)) . '</p>';
					$content .= $string . implode('</p>', array_slice($ct_arr, $count-2, $count));
				break;
				case 'under_p':
					$start = strpos($content, '</p>');
					$content = substr_replace($content, '</p>' . $string, $start, 4);
				break;
				case 'randm_p':
					$rdm = array_rand($ct_arr);
					$content = implode('</p>', array_slice($ct_arr, 0, $rdm)) . '</p>';
					$content .= $string;
					$content .= implode('</p>', array_slice($ct_arr, $rdm, $count-$rdm)) . '</p>';
				break;
				case 'twice_p':
					$content = $ct_arr[0] . '</p>' . $string;
					$content .= implode('</p>', array_slice($ct_arr, 1, $count-3)) . '</p>';
					$content .= $string . implode('</p>', array_slice($ct_arr, $count-2, $count));
				break;
				case 'above_v':
				if(preg_match('#<(object|embed|video|iframe)[^>]+>#sUi', $content, $matches) ) {
					$content = str_replace($matches[0], $string . $matches[0], $content);
				}
				break;
				case 'under_v':
				if(preg_match('#</(?:object|video|iframe)>|</embed>(?!</object>)#sUim', $content, $matches) ) {
					$content = str_replace($matches[0], $matches[0] . $string, $content);
				}
				break;
				case 'twice_v':
				if(preg_match('#<(?:object|video|iframe)[^>]+>.*</(?:object|video|iframe)>|<embed[^>]+>.*</embed>#sUim', $content, $matches) ) {
					$content = str_replace($matches[0], $string . $matches[0] . $string, $content);
				}
				break;
				case 'none':
					$content = $content;
				break;
				default:
					$content = $content;
				break;
				}
			}
		}
		return apply_filters( 'addme_in_content', $content );
	}

	/********************************
	 * Display the options menu
	 */
	function add_admin() {
			$paypal = $this->add_locale_btn();
		?>
<?php
// zone de tests
// echo phpversion();
?>
			<style>
				.rouge { color: red; }
				.green { color: green; }
				.yellow {  background: yellow; }
				.strong { font-weight: bold; }
				.center { text-align: center; }
				.submit { float: right; margin-right: 10%; }
				.exclude { float: right; margin-right: 37%; }
				.cat_id { display: none; }
				select {
					border: 1px inset #888;
					}
				input[type=text], textarea {
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
					padding: 2px;
					border-radius: 3px;
					border: 1px inset #888;
					}
				.paypal {
					float: right;
					font-size: large;
					margin-top: -50px;
					display: block;
				}
					.paypal form { display: inline; }
					.paypal input[type=image] { vertical-align: -30%; }
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('.exclude').text('[<?php _e('SHOW', 'addme'); ?>]');
					$('.exclude').on('click', function(){
						$(this).text() == '[<?php _e('SHOW', 'addme'); ?>]' ? $(this).text('[<?php _e('HIDE', 'addme'); ?>]') : $(this).text('[<?php _e('SHOW', 'addme'); ?>]');
						$(this).closest('tr').nextAll().toggle();
					});
					if ($(location).attr('search').length > 11){
						$(".paypal").fadeIn(2400);
						setInterval(function(){
							$("input[type='image']").fadeTo(1600, 0.6).fadeTo(3200 , 1);
						}, 4800	);
					}
				});
			</script>
			<div class="wrap">
			<h2><?php _e('Add Your Stuff in Post Content Now!', 'addme'); ?></h2>
				<div class="paypal" style="display: none;"><?php _e('Enjoy this plugin?', 'addme'); ?>&nbsp;
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="<?php echo $paypal[0] ?>">
						<input type="image" src="https://www.paypalobjects.com/<?php echo $paypal[1] ?>/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php echo $paypal[2] ?>">
						<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
				<?php for ($i = 1; $i <= $this->add_last_option(); $i++) { ?>
					<form method="POST" action="options.php">
					<?php settings_fields('add_' . $i . '__options_group') ?>
			<table class="form-table">
				<tr>
					<td colspan="1"><label for="add_<?php echo $i ?>__option"><?php _e('Display option:', 'addme'); ?></label></td>
					<td colspan="3"><select name="add_<?php echo $i ?>__option">
					<?php if ('none' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('don\'t display it!', 'addme'); ?></option>
					<?php } else { ?>
						<option value="none"><?php _e('don\'t display it!', 'addme'); ?></option>
					<?php } ?>
					<?php if ('above_c' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('before content', 'addme'); ?></option>
					<?php } else { ?>
						<option value="above_c"><?php _e('before content', 'addme'); ?></option>
					<?php } ?>
					<?php if ('under_c' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('after the content', 'addme'); ?></option>
					<?php } else { ?>
						<option value="under_c"><?php _e('after the content', 'addme'); ?></option>
					<?php } ?>
					<?php if ('twice_c' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('around the post', 'addme'); ?></option>
					<?php } else { ?>
						<option value="twice_c"><?php _e('around the post', 'addme'); ?></option>
					<?php } ?>
					<?php if ('above_p' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('before the last §', 'addme'); ?></option>
					<?php } else { ?>
						<option value="above_p"><?php _e('before the last §', 'addme'); ?></option>
					<?php } ?>
					<?php if ('under_p' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('under the first §', 'addme'); ?></option>
					<?php } else { ?>
						<option value="under_p"><?php _e('under the first §', 'addme'); ?></option>
					<?php } ?>
					<?php if ('randm_p' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('random in post', 'addme'); ?></option>
					<?php } else { ?>
						<option value="randm_p"><?php _e('random in post', 'addme'); ?></option>
					<?php } ?>
					<?php if ('twice_p' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('wrap inner text', 'addme'); ?></option>
					<?php } else { ?>
						<option value="twice_p"><?php _e('wrap inner text', 'addme'); ?></option>
					<?php } ?>
					<?php if ('above_v' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('upon the video', 'addme'); ?></option>
					<?php } else { ?>
						<option value="above_v"><?php _e('upon the video', 'addme'); ?></option>
					<?php } ?>
					<?php if ('under_v' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('under the video', 'addme'); ?></option>
					<?php } else { ?>
						<option value="under_v"><?php _e('under the video', 'addme'); ?></option>
					<?php } ?>
					<?php if ('twice_v' == $this->add_where_todo($i)) { ?>
						<option value="<?php echo $this->add_where_todo($i); ?>" selected><?php _e('wrap the video', 'addme'); ?></option>
					<?php } else { ?>
						<option value="twice_v"><?php _e('wrap the video', 'addme'); ?></option>
					<?php } ?>
					</select></td>
				</tr><tr>
					<td colspan="1"><label for="add_<?php echo $i ?>__preced"><?php _e('Priority:', 'addme'); ?></label></td>
					<td colspan="3"><input type="text" name="add_<?php echo $i ?>__preced" maxlength="5" size="7" value="<?php echo $this->add_is_priority($i); ?>" /> <span class="rouge strong"><?php _e('Be carefull to enter a number between 1 && 9999!', 'addme'); ?></span> </td>
				</tr> <tr>
					<td colspan="1" valign="top"><label for="add_<?php echo $i ?>__string"><?php _e('Something to display:', 'addme'); ?></label></td>
					<td colspan="3"><textarea  name="add_<?php echo $i ?>__string" cols="80" rows="13"><?php echo $this->add_the_string($i); ?></textarea> </td>
				</tr>
				<tr class="trexclu">
				<td><?php _e('To exclude:', 'addme'); ?></td>
				<td><?php _e('Term ID:', 'addme'); ?></td>
				<td><?php _e('Term title:', 'addme'); ?></td>
				<td class="rouge strong exclude">[<?php _e('HIDE', 'addme'); ?>]</td>
				</tr>
				<?php	$terms = $this->add_get_tax_array();
					if ( !empty( $terms ) && !is_wp_error( $terms ) ){
						foreach ( $terms as $term ) {
							echo '<tr class="cat_id"><td><input type="checkbox" name="add_';
							echo $i;
							echo '__exclue[]" value="';
							echo $term->term_id . '"';
							if (in_array($term->term_id, $this->add_who_exclus($i))) {
								echo ' checked';
							}
							echo '> ';
							echo '</td><td>' . $term->term_id;
							echo '</td><td>' . $term->name . '</td></tr>';
						}
					}
				?>
					</table>
					<?php submit_button(); ?>
					</form>
				<?php } // end loop for ?>
			</div>
		<?php
	}

}
$addme = new ContentAdder;
?>