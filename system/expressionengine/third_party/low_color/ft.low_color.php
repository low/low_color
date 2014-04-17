<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Color Fieldtype class
 *
 * @package        low_color
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/
 * @copyright      Copyright (c) 2014, Low
 */
class Low_color_ft extends EE_Fieldtype {

	// --------------------------------------------------------------------
	//  PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Info array
	 *
	 * @access     public
	 * @var        array
	 */
	public $info = array(
		'name'    => 'Low Color',
		'version' => '0.0.1'
	);

	/**
	 * Does fieldtype work in var pair
	 *
	 * @access     public
	 * @var        bool
	 */
	public $has_array_data = FALSE;

	// --------------------------------------------------------------------

	/**
	 * Package name
	 *
	 * @access     private
	 * @var        array
	 */
	private $_package = 'low_color';

	/**
	 * Default settings
	 *
	 * @access     private
	 * @var        array
	 */
	private $_default_settings = array(
		'color' => '',
		'flat' => FALSE,
		'showInput' => TRUE,
		'showInitial' => FALSE,
		'allowEmpty' => TRUE,
		'showAlpha' => FALSE,
		// 'disabled' => FALSE,
		// 'localStorageKey' => '',
		'showPalette' => FALSE,
		'showPaletteOnly' => FALSE,
		'showSelectionPalette' => FALSE,
		'clickoutFiresChange' => FALSE,
		'cancelText' => 'cancel',
		'chooseText' => 'choose',
	);

	/**
	 * Default palette
	 *
	 * @access     private
	 * @var        array
	 */
	private $_default_palette = array(
		'Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Indigo', 'Violet'
	);

	/**
	 * Control Panel assets
	 *
	 * @var        array
	 * @access     private
	 */
	private $_assets = array(
		'vendor/spectrum/spectrum.css',
		'vendor/spectrum/spectrum.js'
	);

	// --------------------------------------------------------------------
	//  METHODS
	// --------------------------------------------------------------------

	/**
	 * Make this type everywhere
	 */
	public function accepts_content_type($type)
	{
		return TRUE;
	}

	/**
	 * Install this fieldtype
	 */
	public function install()
	{
		return array('palette' => $this->_default_palette);
	}

	/**
	 * The global palette
	 */
	public function display_global_settings()
	{
		return form_textarea(array(
			'name'  => 'palette',
			'value' => implode(NL, $this->_get_palette())
		));
	}

	/**
	 * Save the global settings
	 */
	public function save_global_settings()
	{
		$palette = ee()->input->post('palette');
		$palette = array_filter(explode(NL, $palette));

		return array('palette' => $palette);
	}

	/**
	 * Get palette from current settings, fallback to default
	 */
	private function _get_palette()
	{
		return isset($this->settings['palette'])
			? $this->settings['palette']
			: $this->_default_palette;
	}

	// --------------------------------------------------------------------

	/**
	 * General Display Settings method
	 *
	 * @param	array	field settings
	 * @return	string
	 */
	private function _display_settings($settings = array())
	{
		// -------------------------------------
		//  Load language file
		// -------------------------------------

		// ee()->lang->loadfile('low_color');

		// -------------------------------------
		//  Make sure we have all settings
		// -------------------------------------

		$it = array();

		foreach ($this->_default_settings AS $key => $val)
		{
			// What's the setting type?
			$type = gettype($val);

			// Overwrite val if it exists in given settings
			if (array_key_exists($key, $settings))
			{
				$val = $settings[$key];
			}

			// Namespaced setting field name
			$name = $this->_package . "[{$key}]";

			// Build settings
			switch ($type)
			{
				case 'boolean':
					$it[] = array(
						lang($key),
						form_checkbox($name, TRUE, $val)
					);
				break;

				case 'string':
					$it[] = array(
						lang($key),
						form_input($name, $val)
					);
				break;
			}
		}

		// Return the settings
		return $it;
	}

	/**
	 * Display field settings
	 *
	 * @param	array	field settings
	 * @return	string
	 */
	public function display_settings($settings = array())
	{
		foreach ($this->_display_settings($settings) AS $row)
		{
			ee()->table->add_row($row);
		}
	}

	/**
	 * Display the settings in Grid
	 */
	public function grid_display_settings($settings = array())
	{
		$rows = array();

		foreach ($this->_display_settings($settings) AS $row)
		{
			$rows[] = $this->grid_settings_row($row[0], $row[1]);
		}

		return $rows;
	}

	/**
	 * Display the settings in Low Variables
	 */
	public function display_var_settings($settings = array())
	{
		return $this->_display_settings($settings);
	}

	// --------------------------------------------------------------------

	/**
	 * General save settings method
	 */
	private function _save_settings($data)
	{
		$settings = array();

		foreach ($this->_default_settings AS $key => $val)
		{
			// What's the setting type
			$type = gettype($val);

			// Retrieve value from given data
			$val = isset($data[$key]) ? $data[$key] : FALSE;

			// Make sure it's the right type
			settype($val, $type);

			// Add it to the settings
			$settings[$key] = $val;
		}

		return $settings;
	}

	/**
	 * Save field settings
	 *
	 * @access	   public
	 * @param	   array
	 * @return	   array
	 */
	public function save_settings($data)
	{
		return $this->_save_settings(array_merge(
			$data,
			ee()->input->post($this->_package)
		));
	}

	/**
	 * Save settings for grid
	 */
	public function grid_save_settings($data)
	{
		return $this->_save_settings($data[$this->_package]);
	}

	/**
	 * Save Settings for Low Variables
	 */
	public function save_var_settings($data)
	{
		return $this->_save_settings(ee()->input->post($this->_package));
	}

	// --------------------------------------------------------------------

	/**
	 * General display field method
	 */
	private function _display_field($data, $context)
	{
		// -------------------------------------
		// Make sure the CSS and JS is loaded
		// -------------------------------------

		static $loaded;

		if ( ! $loaded)
		{
			$this->_load_assets();
			$loaded = TRUE;
		}

		// -------------------------------------
		// If there is given data, set it to the color-setting
		// -------------------------------------

		if ( ! empty($data))
		{
			$this->settings['color'] = $data;
		}

		// -------------------------------------
		// Get field settings and transform to data-setting-name="setting-value"
		// -------------------------------------

		$settings = array_intersect_key($this->settings, $this->_default_settings);
		$settings['palette'] = htmlspecialchars(json_encode($this->_get_palette()));
		$settings = $this->_data_attrs($settings);

		// -------------------------------------
		// Get other attributes for the input field
		// -------------------------------------

		$attrs = array(
			'type'  => 'text',
			'class' => $this->_package.'_'.$context,
			'name'  => $this->field_name,
			'value' => $data
		);

		// Merge settings and attrs
		$attrs = array_merge($attrs, $settings);

		// -------------------------------------
		//  Build color picker interface
		// -------------------------------------

		$field = '<input '.$this->_attr_string($attrs).' />';

		return $field;
	}

	/**
	 * Display field in publish form
	 *
	 * @param	string	Current value for field
	 * @return	string	HTML containing input field
	 */
	public function display_field($data)
	{
		return $this->_display_field($data, 'field');
	}

	/**
	 * Display the field in a Grid
	 */
	public function grid_display_field($data)
	{
		return $this->_display_field($data, 'grid');
	}

	/**
	 * Display the field in Low Variables
	 */
	public function display_var_field($data)
	{
		return $this->_display_field($data, 'var');
	}

	// --------------------------------------------------------------------

	/**
	 * Display tag in template
	 *
	 * @access      public
	 * @param       string    Current value for field
	 * @param       array     Tag parameters
	 * @param       bool
	 * @return      string
	*/
	// public function replace_tag($data, $params = array(), $tagdata = FALSE)
	// {
	// 	return $data;
	// }

	/**
	 * Output a div
	 */
	public function replace_div($data, $params = array(), $tagdata = FALSE)
	{
		return sprintf(
			'<div style="background-color:%s;width:%spx;height:%spx"></div>',
			$data,
			$params['width'],
			$params['height']
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Load assets: extra JS and CSS
	 *
	 * @access     private
	 * @return     void
	 */
	private function _load_assets()
	{
		// -------------------------------------
		//  Define placeholder
		// -------------------------------------

		$header = $footer = array();

		// -------------------------------------
		//  Loop through assets
		// -------------------------------------

		$asset_url = ((defined('URL_THIRD_THEMES'))
		           ? URL_THIRD_THEMES
		           : ee()->config->item('theme_folder_url') . 'third_party/')
		           . $this->_package . '/';

		foreach ($this->_assets AS $file)
		{
			// location on server
			$file_url = $asset_url.$file.'?v='.$this->info['version'];

			if (substr($file, -3) == 'css')
			{
				$header[] = '<link type="text/css" rel="stylesheet" href="'.$file_url.'" />';
			}
			elseif (substr($file, -2) == 'js')
			{
				$footer[] = '<script type="text/javascript" src="'.$file_url.'"></script>';
			}
		}

		// -------------------------------------
		//  Add combined assets to header/footer
		// -------------------------------------

		if ($header) ee()->cp->add_to_head(implode(NL, $header));
		if ($footer) ee()->cp->add_to_foot(implode(NL, $footer));

		ee()->cp->add_to_foot('<script>
			(function($){
				$(document).ready(function(){

					if (Grid) {
						Grid.bind("low_color", "display", function($cell){
							$cell.find(".low_color_grid").spectrum();
						});
					}

					$(".low_color_field, .low_color_var").spectrum();
				});
			})(jQuery);
		</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Change given settings to attribute string
	 */
	private function _data_attrs($array, $as_string = FALSE)
	{
		$attrs = array();

		foreach ($array AS $key => $val)
		{
			// Convert to data-foo
			$key = 'data-' . strtolower(preg_replace('/([A-Z])/', '-$1', $key));

			switch (gettype($val))
			{
				case 'boolean':
					$val = $val ? 'true' : 'false';
				break;

				// more?
			}

			$attrs[$key] = $val;
		}

		return $as_string ? $this->_attr_string($attrs) : $attrs;
	}

	/**
	 * Attr string
	 */
	private function _attr_string($array)
	{
		$attrs = array();

		foreach ($array AS $key => $val)
		{
			$attrs[] = sprintf('%s="%s"', $key, $val);
		}

		return implode(' ', $attrs);
	}
}
// END Low_color_ft class