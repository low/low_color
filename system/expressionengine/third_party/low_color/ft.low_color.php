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
	 * Control Panel assets
	 *
	 * @var        array
	 * @access     private
	 */
	private $_assets = array(
		'vendor/spectrum/spectrum.css',
		'vendor/spectrum/spectrum.js'
	);

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
		// 'containerClassName' => 'string',
		// 'replacerClassName' => 'string',
		// 'preferredFormat' => 'string',
		// 'maxSelectionSize' => 'int',
		// 'palette' => '[[string]]',
		// 'selectionPalette' => '[string]'
	);

	// --------------------------------------------------------------------
	//  METHODS
	// --------------------------------------------------------------------

	/**
	 * Display field settings
	 *
	 * @param	array	field settings
	 * @return	string
	 */
	public function display_settings($settings = array())
	{
		$rows = $this->_display_settings($settings);

		foreach ($rows AS $row)
		{
			ee()->table->add_row($row);
		}
	}

	/**
	 * Return array with html for setting forms
	 *
	 * @param	array	field settings
	 * @return	string
	 */
	private function _display_settings($settings = array())
	{
		// -------------------------------------
		//  Load language file
		// -------------------------------------

		// ee()->lang->loadfile('low_events');

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

			// Build settings
			switch ($type)
			{
				case 'boolean':
					$it[] = array(
						lang($key),
						form_checkbox($key, TRUE, $val)
					);
				break;

				case 'string':
					$it[] = array(
						lang($key),
						form_input($key, $val)
					);
				break;
			}
		}

		// Return the settings
		return $it;
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
		$settings = array();

		foreach ($this->_default_settings AS $key => $val)
		{
			// What's the setting type
			$type = gettype($val);

			// Retrieve value from POST
			$val = ee()->input->post($key);

			// Make sure it's the right type
			settype($val, $type);

			// Add it to the settings
			$settings[$key] = $val;
		}

		return $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Display field in publish form
	 *
	 * @param	string	Current value for field
	 * @return	string	HTML containing input field
	 */
	public function display_field($data)
	{
		static $loaded;

		if ( ! $loaded)
		{
			$this->_load_assets();
			$loaded = TRUE;
		}

		// -------------------------------------
		//  What's the field name?
		// -------------------------------------

		$settings = array_intersect_key($this->settings, $this->_default_settings);

		$attrs = $this->_data_attrs($settings);

		$attrs = array_merge($attrs, array(
			'type'  => 'text',
			'class' => $this->_package,
			'name'  => $this->field_name,
			'value' => $data
		));


		// -------------------------------------
		//  Build color picker interface
		// -------------------------------------

		$it = '<input '.$this->_attr_string($attrs).' />';

		return $it;
	}

	// --------------------------------------------------------------------

	/**
	 * Validate dates for saving
	 *
	 * @access	   public
	 * @param	   mixed
	 * @return	   mixed
	 */
	public function validate($data)
	{
		// Initiate error message array
		$errors = array();

		// Return error messages or TRUE if none
		return ($errors) ? implode("<br />", $errors) : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Return prepped field data to save
	 *
	 * @param	mixed	Posted data
	 * @return	string	Data to save
	 */
	public function save($data = '')
	{
		return $data;
	}

	/**
	 * Insert/update row into low_events table
	 *
	 * @access     public
	 * @param      mixed     Posted data
	 * @return     void
	 */
	public function post_save($data)
	{
		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Pre-process the given data
	 */
	public function pre_process($data)
	{
		return $data;
	}

	/**
	* Display tag in template
	*
	* @access      public
	* @param       string    Current value for field
	* @param       array     Tag parameters
	* @param       bool
	* @return      string
	*/
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		return $data;
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

		ee()->cp->add_to_foot('<script>(function(){$("input.low_color").spectrum()})();</script>');
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