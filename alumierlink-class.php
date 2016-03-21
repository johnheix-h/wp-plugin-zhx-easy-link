<?php
/*
Class of AlumierLink
Version 1.1
*/
class AlumierLink
{
	// variables for the field and option names 
	private $option_name = 'alumierlinks';
	private $option_value = array();
	private $data_field_text = 'alumierlink_text';
	private $data_field_regex = 'alumierlink_regex';
	private $data_field_url = 'alumierlink_url';
	private $delete_button = "deletelink";
	private $save_button = "savebutton";
	private $limit = 5; // Limit of input lines
	
	// front-end link
	private $hyperLinkTemp = '<a class="alumierlink" href="%s" target="_blank">%s</a>';

	
	public function alumierlink_menu()
	{
		add_options_page('AlumierMD Link', 'AlumierMD Link', 'manage_options', 'alumierlink', array(&$this, 'alumier_link_options'));
	}

	public function alumier_link_options()
	{
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
	
		$this->display_settings_editing_screen();
	}

	private function get_option_value()
	{
		// Read in existing option value from database
		$this->option_value = json_decode(get_option($this->option_name));
		if (! is_array($this->option_value)) {
			$this->option_value = (array) $this->option_value;
		}
		// order by text
		$this->ksort($this->option_value);
	}

	private function deal_with_post()
	{
		$this->deal_with_delete();
		$this->deal_with_save();
	}

	private function deal_with_delete()
	{
		// check delete button 
		if (isset($_POST[$this->delete_button]) && isset($this->option_value[$_POST[$this->delete_button]])) {
			unset($this->option_value[$_POST[$this->delete_button]]);
			update_option($this->option_name, json_encode($this->option_value));
			
			// Put a "setting deleted" message on the screen
			?>
			<div class="updated"><p><strong><?php _e('setting deleted.', 'alumierlink' ); ?></strong></p></div>
			<?php
		}
	}

	private function deal_with_save()
	{
		// See if the user has posted us some information
		if (!empty($_POST[$this->save_button])) {
			$text_posts = $_POST[$this->data_field_text];
			$regex_posts = $_POST[$this->data_field_regex];
			$url_posts = $_POST[$this->data_field_url];
			for ($i = 0; $i < $this->limit; $i++) {
				// Read their posted value
				$text = $text_posts[$i];
				//$text = str_replace(array("\\",'"'), '', $text_posts[$i]);
				$regex = $regex_posts[$i];
				$url = $url_posts[$i];
				if (empty($text)) {
					continue;
				}
				// Save the posted value in the database
				$this->option_value["$text"] = array('url' => $url, 'regex' => $regex);
			}
			update_option($this->option_name, json_encode($this->option_value));

			// Put a "settings saved" message on the screen
			?>
			<div class="updated"><p><strong><?php _e('settings saved.', 'alumierlink' ); ?></strong></p></div>
			<?php
			$this->ksort($this->option_value);
		}
	}

	private function display_settings_editing_screen()
	{
		// Now display the settings editing screen
		?>
		<div class="wrap">
			<!-- // header -->
			<h2><?php _e( 'AlumierMD Link Settings', 'alumierlink' ) ?></h2>
			<?php
			$this->get_option_value();
			$this->deal_with_post();
			$this->display_settings_form();
			$this->display_existing_links_list();
			?>
		</div>
		<?php
	}
	
	private function display_settings_form()
	{
		?>
		<!-- // settings form -->
		<form name="form1" method="post" action="">
			<input type="hidden" name="<?php esc_attr_e($this->save_button) ?>" value="save" />
			<table class="form-table">
				<tbody>
					<tr>
						<th><label><?php _e("Text:", 'alumierlink' ); ?></label></th>
						<th><label><?php _e("Regular Expression:", 'alumierlink' ); ?></label></th>
						<th><label><?php _e("Url:", 'alumierlink' ); ?></label></th>
					</tr>
					<tr>
						<td>(Single and double quotes are not allowed)</td>
						<td>(Leave it blank if it is not necessary)</td>
						<td>(Start with http please)</td>
					</tr>
					<?php for ($i = 0; $i < $this->limit; $i++) { ?> 
					<tr>
						<td><input type="text" name="<?php echo $this->data_field_text; ?>[<?php echo $i ?>]" id="<?php echo $this->data_field_text; ?>[<?php echo $i ?>]" value="" size="40" placeholder="Text"></td>
						<td><input type="text" name="<?php echo $this->data_field_regex; ?>[<?php echo $i ?>]" id="<?php echo $this->data_field_regex; ?>[<?php echo $i ?>]" value="" size="40" placeholder="Regular Expression"></td>
						<td><input type="text" name="<?php echo $this->data_field_url; ?>[<?php echo $i ?>]" id="<?php echo $this->data_field_url; ?>[<?php echo $i ?>]" value="" size="40" placeholder="Url"></td>
					</tr>
					<?php } ?>
					<tr colspan="2">
						<td><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save') ?>" /></td>
					</tr>
				</tbody>
			</table>
			<hr />
		</form>
		<?php
	}

	private function display_existing_links_list()
	{
		?>
		<!-- // display existing links -->
		<style>
			tr.alumier-link-list td, tr.alumier-link-list th {border: 1px solid #ccc;}
			tr.alumier-link-list th {text-indent:15px;}
		</style>
		<table class="form-table">
			<tbody>
				<tr class="alumier-link-list">
					<th>Action</th><th>Text</th><th>Regular Expression</th><th>Url</th>
				</tr>
				<?php
				$evenOdd = "odd";
				foreach ($this->option_value as $text => $urlAndRegex) {
					if (is_string($urlAndRegex)) { // compatible with previous version 1.0
						$item['url'] = $urlAndRegex;
						$item['regex'] = '';
					} else {
						$item = (array)$urlAndRegex;
					}
					$url = $item['url'];
					$regex = $item['regex'];
					$evenOdd = $evenOdd == "odd" ? "even" : "odd";
				?>
					<form method="post" action="">
						<tr class="alumier-link-list <?php _e($evenOdd) ?>">
							<td>
								<input type="hidden" name="<?php esc_attr_e($this->delete_button) ?>" value="<?php esc_attr_e($text) ?>" />
								<input type="submit" name="Delete" class="button-secondary" value="<?php esc_attr_e('Delete') ?>" />
							</td>
							<td><?php _e($text, "alumierlink"); ?></td>
							<td><code><?php _e($regex, "alumierlink"); ?></code></td>
							<td><code><?php _e($url, "alumierlink"); ?></code></td>
						</tr>
					</form>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
    /**
     * Sorts an array by key case-insensitive, maintaining key to data correlations. 
     * This is useful mainly for associative arrays.
     * 
     * @param array $array_arg Array to be sorted by key case-insensitive
     */
    private function ksort(&$array_arg)
    {        
        uksort($array_arg, array($this, "_strcmp"));
    }
	private function _strcmp($a, $b)
	{
		return strcasecmp($a, $b);
	}


	
	
	
	
	
	
	
	
	/**
	 * front-end
	 * \\ and \" cannot be used inside text string. 
	 * Text format could be text||anythingelse. In this case, "||anythingelse" will be discarded while display text.
	 * Regular expression field can be used for special chars in text.
	 */
	public function alumierlink_interpreter($content)
	{
		if (empty($content)) {
			return $content;
		}
		
		$this->option_value = json_decode(get_option( $this->option_name ));
		if (!is_array($this->option_value)) {
			$this->option_value = (array) $this->option_value;
		}
		$text_keys = array();
		$regex_keys = array();
		$url_val = array();
		foreach ($this->option_value as $k => $val) {
			if (is_string($val)) {
				$v['url'] = $val;
				$v['regex'] = '';
			} else {
				$v = (array) $val;
			}
			if (empty($v['regex'])) {
				$v['regex'] = preg_replace('/[^\w\d]+/', '.+?', html_entity_decode($k));
			}
			$regex_keys[] = '/\{\{' . $v['regex'] . '\}\}/'; 
			$url_val[] = sprintf($this->hyperLinkTemp, $v['url'], current(explode('||', $k)));
		}
		$content = preg_replace($regex_keys, $url_val, $content);
		
		return $content;
	}
}