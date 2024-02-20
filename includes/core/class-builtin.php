<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Builtin')) {

	/**
	 * Class Builtin
	 * @package wams\core
	 */
	class Builtin
	{

		/**
		 * @var array
		 */
		public $core_fields = array();

		/**
		 * @var array
		 */
		public $fields_dropdown = array();

		/**
		 * Builtin constructor.
		 */
		public function __construct()
		{
			add_action('init', array(&$this, 'set_core_fields'), 1);
		}


		/**
		 * Regular or multi-select/options
		 *
		 * @param $field
		 * @param $attrs
		 *
		 * @return bool
		 */
		function is_dropdown_field($field, $attrs)
		{

			if (isset($attrs['options'])) {
				return true;
			}

			if (isset($fields[$field]['options']) || !empty($fields[$field]['custom_dropdown_options_source'])) {
				return true;
			}

			return false;
		}


		/**
		 * Get a field
		 *
		 * @param $field
		 *
		 * @return mixed|string
		 */
		function get_a_field($field)
		{
			if (isset($fields[$field])) {
				return $fields[$field];
			}
			return '';
		}




		/**
		 * Checks for a unique field error
		 *
		 * @param $key
		 *
		 * @return int|string
		 */
		public function unique_field_err($key)
		{
			if (empty($key)) {
				return __('Please provide a meta key', 'wams');
			}
			if (isset($this->core_fields[$key])) {
				return __('Your meta key is a reserved core field and cannot be used', 'wams');
			}

			if (!WAMS()->validation()->safe_string($key)) {
				return __('Your meta key contains illegal characters. Please correct it.', 'wams');
			}

			return 0;
		}


		/**
		 * Check date range errors (start date)
		 *
		 * @param $date
		 *
		 * @return int|string
		 */
		function date_range_start_err($date)
		{
			if (empty($date)) {
				return __('Please provide a date range beginning', 'wams');
			}
			if (!WAMS()->validation()->validate_date($date)) {
				return __('Please enter a valid start date in the date range', 'wams');
			}

			return 0;
		}


		/**
		 * Check date range errors (end date)
		 *
		 * @param $date
		 * @param $start_date
		 *
		 * @return int|string
		 */
		function date_range_end_err($date, $start_date)
		{
			if (empty($date)) {
				return __('Please provide a date range end', 'wams');
			}
			if (!WAMS()->validation()->validate_date($date)) {
				return __('Please enter a valid end date in the date range', 'wams');
			}
			if (strtotime($date) <= strtotime($start_date)) {
				return __('The end of date range must be greater than the start of date range', 'wams');
			}
			return 0;
		}

		/**
		 * Get a core field attrs.
		 *
		 * @param string $type Field type.
		 *
		 * @return array Field data.
		 */
		public function get_core_field_attrs($type)
		{
			return array_key_exists($type, $this->core_fields) ? $this->core_fields[$type] : array('');
		}

		/**
		 * Core Fields
		 */
		public function set_core_fields()
		{
			$this->core_fields = array(
				'row' => array(
					'name' => 'Row',
					'in_fields' => false,
					'form_only' => true,
					'conditional_support' => 0,
					'icon' => 'um-faicon-pencil',
					'col1' => array('_id', '_background', '_text_color', '_padding', '_margin', '_border', '_borderradius', '_borderstyle', '_bordercolor'),
					'col2' => array('_heading', '_heading_text', '_heading_background_color', '_heading_text_color', '_icon', '_icon_color', '_css_class'),
				),

				'text' => array(
					'name' => 'Text Box',
					'col1' => array('_title', '_metakey', '_help', '_default', '_min_chars', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate', '_max_chars'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'tel' => array(
					'name' => __('Telephone Box', 'wams'),
					'col1' => array('_title', '_metakey', '_help', '_default', '_min_chars', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate', '_max_chars'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams'),
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'number' => array(
					'name' => __('Number', 'wams'),
					'col1' => array('_title', '_metakey', '_help', '_default', '_min', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate', '_max'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'textarea' => array(
					'name' => 'Textarea',
					'col1' => array('_title', '_metakey', '_help', '_height', '_max_chars', '_max_words', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_default', '_html'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'select' => array(
					'name' => 'Dropdown',
					'col1' => array('_title', '_metakey', '_help', '_default', '_options', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_custom_dropdown_options_source', '_parent_dropdown_relationship'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_options' => array(
							'mode' => 'required',
							'error' => __('You have not added any choices yet.', 'wams')
						),
					)
				),

				'multiselect' => array(
					'name' => 'Multi-Select',
					'col1' => array('_title', '_metakey', '_help', '_default', '_options', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_min_selections', '_max_selections', '_custom_dropdown_options_source'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_options' => array(
							'mode' => 'required',
							'error' => __('You have not added any choices yet.', 'wams')
						),
					)
				),

				'radio' => array(
					'name' => 'Radio',
					'col1' => array('_title', '_metakey', '_help', '_default', '_options', '_visibility'),
					'col2' => array('_label', '_public', '_roles'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_options' => array(
							'mode' => 'required',
							'error' => __('You have not added any choices yet.', 'wams')
						),
					)
				),

				'checkbox' => array(
					'name' => 'Checkbox',
					'col1' => array('_title', '_metakey', '_help', '_default', '_options', '_visibility'),
					'col2' => array('_label', '_public', '_roles', '_max_selections'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_options' => array(
							'mode' => 'required',
							'error' => __('You have not added any choices yet.', 'wams')
						),
					)
				),

				'url' => array(
					'name' => 'URL',
					'col1' => array('_title', '_metakey', '_help', '_default', '_url_text', '_visibility'),
					'col2' => array('_label', '_placeholder', '_url_target', '_url_rel', '_public', '_roles', '_validate', '_custom_validate'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'password' => array(
					'name' => 'Password',
					'col1' => array('_title', '_metakey', '_help', '_min_chars', '_max_chars', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_force_good_pass', '_force_confirm_pass', '_label_confirm_pass'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'image' => array(
					'name' => 'Image Upload',
					'col1' => array('_title', '_metakey', '_help', '_allowed_types', '_max_size', '_crop', '_visibility'),
					'col2' => array('_label', '_public', '_roles', '_upload_text', '_upload_help_text', '_button_text'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_max_size' => array(
							'mode' => 'numeric',
							'error' => __('Please enter a valid size', 'wams')
						),
					)
				),

				'file' => array(
					'name' => 'File Upload',
					'col1' => array('_title', '_metakey', '_help', '_allowed_types', '_max_size', '_visibility'),
					'col2' => array('_label', '_public', '_roles', '_upload_text', '_upload_help_text', '_button_text'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
						'_max_size' => array(
							'mode' => 'numeric',
							'error' => __('Please enter a valid size', 'wams')
						),
					)
				),

				'date' => array(
					'name'     => 'Date Picker',
					'col1'     => array('_title', '_metakey', '_help', '_default', '_range', '_years', '_years_x', '_range_start', '_range_end', '_visibility'),
					'col2'     => array('_label', '_placeholder', '_public', '_roles', '_format', '_format_custom', '_pretty_format', '_disabled_weekdays'),
					'col3'     => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title'       => array(
							'mode'  => 'required',
							'error' => __('You must provide a title', 'wams'),
						),
						'_metakey'     => array(
							'mode' => 'unique',
						),
						'_years'       => array(
							'mode'  => 'numeric',
							'error' => __('Number of years is not valid', 'wams'),
						),
						'_range_start' => array(
							'mode' => 'range-start',
						),
						'_range_end'   => array(
							'mode' => 'range-end',
						),
					),
				),

				'time' => array(
					'name' => 'Time Picker',
					'col1' => array('_title', '_metakey', '_help', '_format', '_visibility'),
					'col2' => array('_label', '_placeholder', '_default', '_public', '_roles', '_intervals'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'rating' => array(
					'name' => 'Rating',
					'col1' => array('_title', '_metakey', '_help', '_visibility'),
					'col2' => array('_label', '_public', '_roles', '_number', '_default'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'block' => array(
					'name' => 'Content Block',
					'col1' => array('_title', '_visibility'),
					'col2' => array('_public', '_roles'),
					'col_full' => array('_content'),
					'mce_content' => true,
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
					)
				),

				'shortcode' => array(
					'name' => 'Shortcode',
					'col1' => array('_title', '_visibility'),
					'col2' => array('_public', '_roles'),
					'col_full' => array('_content'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_content' => array(
							'mode' => 'required',
							'error' => __('You must add a shortcode to the content area', 'wams')
						),
					)
				),

				'spacing' => array(
					'name' => 'Spacing',
					'col1' => array('_title', '_visibility'),
					'col2' => array('_spacing'),
					'form_only' => true,
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
					)
				),

				'divider' => array(
					'name' => 'Divider',
					'col1' => array('_title', '_width', '_divider_text', '_visibility'),
					'col2' => array('_style', '_color', '_public', '_roles'),
					'form_only' => true,
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
					)
				),

				'googlemap' => array(
					'name' => 'Google Map',
					'col1' => array('_title', '_metakey', '_help', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'youtube_video' => array(
					'name' => 'YouTube Video',
					'col1' => array('_title', '_metakey', '_help', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'vimeo_video' => array(
					'name' => 'Vimeo Video',
					'col1' => array('_title', '_metakey', '_help', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams')
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					)
				),

				'soundcloud_track' => array(
					'name' => 'SoundCloud Track',
					'col1' => array('_title', '_metakey', '_help', '_visibility'),
					'col2' => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3' => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => __('You must provide a title', 'wams'),
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					),
				),
				'spotify' => array(
					'name'     => __('Spotify URL', 'wams'),
					'col1'     => array('_title', '_metakey', '_help', '_visibility'),
					'col2'     => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3'     => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title'   => array(
							'mode'  => 'required',
							'error' => __('You must provide a title', 'wams'),
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					),
				),
				'oembed'           => array(
					'name'     => __('oEmbed', 'wams'),
					'col1'     => array('_title', '_metakey', '_help', '_default', '_visibility'),
					'col2'     => array('_label', '_placeholder', '_public', '_roles', '_validate', '_custom_validate'),
					'col3'     => array('_required', '_editable', '_icon'),
					'validate' => array(
						'_title'   => array(
							'mode'  => 'required',
							'error' => __('You must provide a title', 'wams'),
						),
						'_metakey' => array(
							'mode' => 'unique',
						),
					),
				),

				/*'group' => array(
					'name' => 'Field Group',
					'col1' => array('_title','_max_entries'),
					'col2' => array('_label','_public','_roles'),
					'form_only' => true,
					'validate' => array(
						'_title' => array(
							'mode' => 'required',
							'error' => 'You must provide a title'
						),
						'_label' => array(
							'mode' => 'required',
							'error' => 'You must provide a label'
						),
					)
				),*/

			);
		}




		/**
		 * Possible validation types for fields
		 *
		 * @return mixed
		 */
		function validation_types()
		{

			$array[0]                          = __('None', 'wams');
			$array['alphabetic']               = __('Alphabetic value only', 'wams');
			$array['alpha_numeric']            = __('Alpha-numeric value', 'wams');
			$array['english']                  = __('English letters only', 'wams');
			$array['facebook_url']             = __('Facebook URL', 'wams');
			$array['instagram_url']            = __('Instagram URL', 'wams');
			$array['linkedin_url']             = __('LinkedIn URL', 'wams');
			$array['lowercase']                = __('Lowercase only', 'wams');
			$array['numeric']                  = __('Numeric value only', 'wams');
			$array['phone_number']             = __('Phone Number', 'wams');
			$array['skype']                    = __('Skype ID', 'wams');
			$array['soundcloud']               = __('SoundCloud Profile', 'wams');
			$array['twitter_url']              = __('Twitter URL', 'wams');
			$array['is_email']                 = __('E-mail( Not Unique )', 'wams');
			$array['unique_email']             = __('Unique E-mail', 'wams');
			$array['unique_value']             = __('Unique Metakey value', 'wams');
			$array['unique_username']          = __('Unique Username', 'wams');
			$array['unique_username_or_email'] = __('Unique Username/E-mail', 'wams');
			$array['url']                      = __('Website URL', 'wams');
			$array['youtube_url']              = __('YouTube Profile', 'wams');
			$array['youtube_video']            = __('YouTube Video', 'wams');
			$array['spotify_url']              = __('Spotify URL', 'wams');
			$array['telegram_url']             = __('Telegram URL', 'wams');
			$array['discord']                  = __('Discord ID', 'wams');
			$array['tiktok_url']               = __('TikTok URL', 'wams');
			$array['twitch_url']               = __('Twitch URL', 'wams');
			$array['reddit_url']               = __('Reddit URL', 'wams');
			$array['custom']                   = __('Custom Validation', 'wams');

			return $array;
		}


		/**
		 * Get predefined options
		 *
		 * @param $data
		 *
		 * @return array|mixed|void
		 */
		function get($data)
		{
			switch ($data) {

				case 'languages':
					$array = array(
						"aa" => __("Afar", 'wams'),
						"ab" => __("Abkhazian", 'wams'),
						"ae" => __("Avestan", 'wams'),
						"af" => __("Afrikaans", 'wams'),
						"ak" => __("Akan", 'wams'),
						"am" => __("Amharic", 'wams'),
						"an" => __("Aragonese", 'wams'),
						"ar" => __("Arabic", 'wams'),
						"as" => __("Assamese", 'wams'),
						"av" => __("Avaric", 'wams'),
						"ay" => __("Aymara", 'wams'),
						"az" => __("Azerbaijani", 'wams'),
						"ba" => __("Bashkir", 'wams'),
						"be" => __("Belarusian", 'wams'),
						"bg" => __("Bulgarian", 'wams'),
						"bh" => __("Bihari", 'wams'),
						"bi" => __("Bislama", 'wams'),
						"bm" => __("Bambara", 'wams'),
						"bn" => __("Bengali", 'wams'),
						"bo" => __("Tibetan", 'wams'),
						"br" => __("Breton", 'wams'),
						"bs" => __("Bosnian", 'wams'),
						"ca" => __("Catalan", 'wams'),
						"ce" => __("Chechen", 'wams'),
						"ch" => __("Chamorro", 'wams'),
						"co" => __("Corsican", 'wams'),
						"cr" => __("Cree", 'wams'),
						"cs" => __("Czech", 'wams'),
						"cu" => __("Church Slavic", 'wams'),
						"cv" => __("Chuvash", 'wams'),
						"cy" => __("Welsh", 'wams'),
						"da" => __("Danish", 'wams'),
						"de" => __("German", 'wams'),
						"dv" => __("Divehi", 'wams'),
						"dz" => __("Dzongkha", 'wams'),
						"ee" => __("Ewe", 'wams'),
						"el" => __("Greek", 'wams'),
						"en" => __("English", 'wams'),
						"eo" => __("Esperanto", 'wams'),
						"es" => __("Spanish", 'wams'),
						"et" => __("Estonian", 'wams'),
						"eu" => __("Basque", 'wams'),
						"fa" => __("Persian", 'wams'),
						"ff" => __("Fulah", 'wams'),
						"fi" => __("Finnish", 'wams'),
						"fj" => __("Fijian", 'wams'),
						"fo" => __("Faroese", 'wams'),
						"fr" => __("French", 'wams'),
						"fy" => __("Western Frisian", 'wams'),
						"ga" => __("Irish", 'wams'),
						"gd" => __("Scottish Gaelic", 'wams'),
						"gl" => __("Galician", 'wams'),
						"gn" => __("Guarani", 'wams'),
						"gu" => __("Gujarati", 'wams'),
						"gv" => __("Manx", 'wams'),
						"ha" => __("Hausa", 'wams'),
						"he" => __("Hebrew", 'wams'),
						"hi" => __("Hindi", 'wams'),
						"ho" => __("Hiri Motu", 'wams'),
						"hr" => __("Croatian", 'wams'),
						"ht" => __("Haitian", 'wams'),
						"hu" => __("Hungarian", 'wams'),
						"hy" => __("Armenian", 'wams'),
						"hz" => __("Herero", 'wams'),
						"ia" => __("Interlingua (International Auxiliary Language Association)", 'wams'),
						"id" => __("Indonesian", 'wams'),
						"ie" => __("Interlingue", 'wams'),
						"ig" => __("Igbo", 'wams'),
						"ii" => __("Sichuan Yi", 'wams'),
						"ik" => __("Inupiaq", 'wams'),
						"io" => __("Ido", 'wams'),
						"is" => __("Icelandic", 'wams'),
						"it" => __("Italian", 'wams'),
						"iu" => __("Inuktitut", 'wams'),
						"ja" => __("Japanese", 'wams'),
						"jv" => __("Javanese", 'wams'),
						"ka" => __("Kartuli", 'wams'),
						"kg" => __("Kongo", 'wams'),
						"ki" => __("Kikuyu", 'wams'),
						"kj" => __("Kwanyama", 'wams'),
						"kk" => __("Kazakh", 'wams'),
						"kl" => __("Kalaallisut", 'wams'),
						"km" => __("Khmer", 'wams'),
						"kn" => __("Kannada", 'wams'),
						"ko" => __("Korean", 'wams'),
						"kr" => __("Kanuri", 'wams'),
						"ks" => __("Kashmiri", 'wams'),
						"ku" => __("Kurdish", 'wams'),
						"kv" => __("Komi", 'wams'),
						"kw" => __("Cornish", 'wams'),
						"ky" => __("Kirghiz", 'wams'),
						"la" => __("Latin", 'wams'),
						"lb" => __("Luxembourgish", 'wams'),
						"lg" => __("Ganda", 'wams'),
						"li" => __("Limburgish", 'wams'),
						"ln" => __("Lingala", 'wams'),
						"lo" => __("Lao", 'wams'),
						"lt" => __("Lithuanian", 'wams'),
						"lu" => __("Luba-Katanga", 'wams'),
						"lv" => __("Latvian", 'wams'),
						"mg" => __("Malagasy", 'wams'),
						"mh" => __("Marshallese", 'wams'),
						"mi" => __("Maori", 'wams'),
						"mk" => __("Macedonian", 'wams'),
						"ml" => __("Malayalam", 'wams'),
						"mn" => __("Mongolian", 'wams'),
						"mr" => __("Marathi", 'wams'),
						"ms" => __("Malay", 'wams'),
						"mt" => __("Maltese", 'wams'),
						"my" => __("Burmese", 'wams'),
						"na" => __("Nauru", 'wams'),
						"nb" => __("Norwegian Bokmal", 'wams'),
						"nd" => __("North Ndebele", 'wams'),
						"ne" => __("Nepali", 'wams'),
						"ng" => __("Ndonga", 'wams'),
						"nl" => __("Dutch", 'wams'),
						"nn" => __("Norwegian Nynorsk", 'wams'),
						"no" => __("Norwegian", 'wams'),
						"nr" => __("South Ndebele", 'wams'),
						"nv" => __("Navajo", 'wams'),
						"ny" => __("Chichewa", 'wams'),
						"oc" => __("Occitan", 'wams'),
						"oj" => __("Ojibwa", 'wams'),
						"om" => __("Oromo", 'wams'),
						"or" => __("Oriya", 'wams'),
						"os" => __("Ossetian", 'wams'),
						"pa" => __("Panjabi", 'wams'),
						"pi" => __("Pali", 'wams'),
						"pl" => __("Polish", 'wams'),
						"ps" => __("Pashto", 'wams'),
						"pt" => __("Portuguese", 'wams'),
						"qu" => __("Quechua", 'wams'),
						"rm" => __("Raeto-Romance", 'wams'),
						"rn" => __("Kirundi", 'wams'),
						"ro" => __("Romanian", 'wams'),
						"ru" => __("Russian", 'wams'),
						"rw" => __("Kinyarwanda", 'wams'),
						"sa" => __("Sanskrit", 'wams'),
						"sc" => __("Sardinian", 'wams'),
						"sd" => __("Sindhi", 'wams'),
						"se" => __("Northern Sami", 'wams'),
						"sg" => __("Sango", 'wams'),
						"si" => __("Sinhala", 'wams'),
						"sk" => __("Slovak", 'wams'),
						"sl" => __("Slovenian", 'wams'),
						"sm" => __("Samoan", 'wams'),
						"sn" => __("Shona", 'wams'),
						"so" => __("Somali", 'wams'),
						"sq" => __("Albanian", 'wams'),
						"sr" => __("Serbian", 'wams'),
						"ss" => __("Swati", 'wams'),
						"st" => __("Southern Sotho", 'wams'),
						"su" => __("Sundanese", 'wams'),
						"sv" => __("Swedish", 'wams'),
						"sw" => __("Swahili", 'wams'),
						"ta" => __("Tamil", 'wams'),
						"te" => __("Telugu", 'wams'),
						"tg" => __("Tajik", 'wams'),
						"th" => __("Thai", 'wams'),
						"ti" => __("Tigrinya", 'wams'),
						"tk" => __("Turkmen", 'wams'),
						"tl" => __("Tagalog", 'wams'),
						"tn" => __("Tswana", 'wams'),
						"to" => __("Tonga", 'wams'),
						"tr" => __("Turkish", 'wams'),
						"ts" => __("Tsonga", 'wams'),
						"tt" => __("Tatar", 'wams'),
						"tw" => __("Twi", 'wams'),
						"ty" => __("Tahitian", 'wams'),
						"ug" => __("Uighur", 'wams'),
						"uk" => __("Ukrainian", 'wams'),
						"ur" => __("Urdu", 'wams'),
						"uz" => __("Uzbek", 'wams'),
						"ve" => __("Venda", 'wams'),
						"vi" => __("Vietnamese", 'wams'),
						"vo" => __("Volapuk", 'wams'),
						"wa" => __("Walloon", 'wams'),
						"wo" => __("Wolof", 'wams'),
						"xh" => __("Xhosa", 'wams'),
						"yi" => __("Yiddish", 'wams'),
						"yo" => __("Yoruba", 'wams'),
						"za" => __("Zhuang", 'wams'),
						"zh" => __("Chinese", 'wams'),
						"zu" => __("Zulu", 'wams')
					);
					break;

				case 'countries':
					$array = array(
						'AF' => __('Afghanistan', 'wams'),
						'AX' => __('Åland Islands', 'wams'),
						'AL' => __('Albania', 'wams'),
						'DZ' => __('Algeria', 'wams'),
						'AS' => __('American Samoa', 'wams'),
						'AD' => __('Andorra', 'wams'),
						'AO' => __('Angola', 'wams'),
						'AI' => __('Anguilla', 'wams'),
						'AQ' => __('Antarctica', 'wams'),
						'AG' => __('Antigua and Barbuda', 'wams'),
						'AR' => __('Argentina', 'wams'),
						'AM' => __('Armenia', 'wams'),
						'AW' => __('Aruba', 'wams'),
						'AU' => __('Australia', 'wams'),
						'AT' => __('Austria', 'wams'),
						'AZ' => __('Azerbaijan', 'wams'),
						'BS' => __('Bahamas', 'wams'),
						'BH' => __('Bahrain', 'wams'),
						'BD' => __('Bangladesh', 'wams'),
						'BB' => __('Barbados', 'wams'),
						'BY' => __('Belarus', 'wams'),
						'BE' => __('Belgium', 'wams'),
						'BZ' => __('Belize', 'wams'),
						'BJ' => __('Benin', 'wams'),
						'BM' => __('Bermuda', 'wams'),
						'BT' => __('Bhutan', 'wams'),
						'BO' => __('Bolivia, Plurinational State of', 'wams'),
						'BA' => __('Bosnia and Herzegovina', 'wams'),
						'BW' => __('Botswana', 'wams'),
						'BV' => __('Bouvet Island', 'wams'),
						'BR' => __('Brazil', 'wams'),
						'IO' => __('British Indian Ocean Territory', 'wams'),
						'BN' => __('Brunei Darussalam', 'wams'),
						'BG' => __('Bulgaria', 'wams'),
						'BF' => __('Burkina Faso', 'wams'),
						'BI' => __('Burundi', 'wams'),
						'KH' => __('Cambodia', 'wams'),
						'CM' => __('Cameroon', 'wams'),
						'CA' => __('Canada', 'wams'),
						'CV' => __('Cape Verde', 'wams'),
						'KY' => __('Cayman Islands', 'wams'),
						'CF' => __('Central African Republic', 'wams'),
						'TD' => __('Chad', 'wams'),
						'CL' => __('Chile', 'wams'),
						'CN' => __('China', 'wams'),
						'CX' => __('Christmas Island', 'wams'),
						'CC' => __('Cocos (Keeling) Islands', 'wams'),
						'CO' => __('Colombia', 'wams'),
						'KM' => __('Comoros', 'wams'),
						'CG' => __('Congo', 'wams'),
						'CD' => __('Congo, the Democratic Republic of the', 'wams'),
						'CK' => __('Cook Islands', 'wams'),
						'CR' => __('Costa Rica', 'wams'),
						'CI' => __("Côte d'Ivoire", 'wams'),
						'HR' => __('Croatia', 'wams'),
						'CU' => __('Cuba', 'wams'),
						'CY' => __('Cyprus', 'wams'),
						'CZ' => __('Czech Republic', 'wams'),
						'DK' => __('Denmark', 'wams'),
						'DJ' => __('Djibouti', 'wams'),
						'DM' => __('Dominica', 'wams'),
						'DO' => __('Dominican Republic', 'wams'),
						'EC' => __('Ecuador', 'wams'),
						'EG' => __('Egypt', 'wams'),
						'SV' => __('El Salvador', 'wams'),
						'GQ' => __('Equatorial Guinea', 'wams'),
						'ER' => __('Eritrea', 'wams'),
						'EE' => __('Estonia', 'wams'),
						'ET' => __('Ethiopia', 'wams'),
						'FK' => __('Falkland Islands (Malvinas)', 'wams'),
						'FO' => __('Faroe Islands', 'wams'),
						'FJ' => __('Fiji', 'wams'),
						'FI' => __('Finland', 'wams'),
						'FR' => __('France', 'wams'),
						'GF' => __('French Guiana', 'wams'),
						'PF' => __('French Polynesia', 'wams'),
						'TF' => __('French Southern Territories', 'wams'),
						'GA' => __('Gabon', 'wams'),
						'GM' => __('Gambia', 'wams'),
						'GE' => __('Sakartvelo', 'wams'),
						'DE' => __('Germany', 'wams'),
						'GH' => __('Ghana', 'wams'),
						'GI' => __('Gibraltar', 'wams'),
						'GR' => __('Greece', 'wams'),
						'GL' => __('Greenland', 'wams'),
						'GD' => __('Grenada', 'wams'),
						'GP' => __('Guadeloupe', 'wams'),
						'GU' => __('Guam', 'wams'),
						'GT' => __('Guatemala', 'wams'),
						'GG' => __('Guernsey', 'wams'),
						'GN' => __('Guinea', 'wams'),
						'GW' => __('Guinea-Bissau', 'wams'),
						'GY' => __('Guyana', 'wams'),
						'HT' => __('Haiti', 'wams'),
						'HM' => __('Heard Island and McDonald Islands', 'wams'),
						'VA' => __('Holy See (Vatican City State)', 'wams'),
						'HN' => __('Honduras', 'wams'),
						'HK' => __('Hong Kong', 'wams'),
						'HU' => __('Hungary', 'wams'),
						'IS' => __('Iceland', 'wams'),
						'IN' => __('India', 'wams'),
						'ID' => __('Indonesia', 'wams'),
						'IR' => __('Iran, Islamic Republic of', 'wams'),
						'IQ' => __('Iraq', 'wams'),
						'IE' => __('Ireland', 'wams'),
						'IM' => __('Isle of Man', 'wams'),
						'IL' => __('Israel', 'wams'),
						'IT' => __('Italy', 'wams'),
						'JM' => __('Jamaica', 'wams'),
						'JP' => __('Japan', 'wams'),
						'JE' => __('Jersey', 'wams'),
						'JO' => __('Jordan', 'wams'),
						'KZ' => __('Kazakhstan', 'wams'),
						'KE' => __('Kenya', 'wams'),
						'KI' => __('Kiribati', 'wams'),
						'KP' => __("Korea, Democratic People's Republic of", 'wams'),
						'KR' => __('Korea, Republic of', 'wams'),
						'KW' => __('Kuwait', 'wams'),
						'KG' => __('Kyrgyzstan', 'wams'),
						'LA' => __("Lao People's Democratic Republic", 'wams'),
						'LV' => __('Latvia', 'wams'),
						'LB' => __('Lebanon', 'wams'),
						'LS' => __('Lesotho', 'wams'),
						'LR' => __('Liberia', 'wams'),
						'LY' => __('Libyan Arab Jamahiriya', 'wams'),
						'LI' => __('Liechtenstein', 'wams'),
						'LT' => __('Lithuania', 'wams'),
						'LU' => __('Luxembourg', 'wams'),
						'MO' => __('Macao', 'wams'),
						'MK' => __('Macedonia, the former Yugoslav Republic of', 'wams'),
						'MG' => __('Madagascar', 'wams'),
						'MW' => __('Malawi', 'wams'),
						'MY' => __('Malaysia', 'wams'),
						'MV' => __('Maldives', 'wams'),
						'ML' => __('Mali', 'wams'),
						'MT' => __('Malta', 'wams'),
						'MH' => __('Marshall Islands', 'wams'),
						'MQ' => __('Martinique', 'wams'),
						'MR' => __('Mauritania', 'wams'),
						'MU' => __('Mauritius', 'wams'),
						'YT' => __('Mayotte', 'wams'),
						'MX' => __('Mexico', 'wams'),
						'FM' => __('Micronesia, Federated States of', 'wams'),
						'MD' => __('Moldova, Republic of', 'wams'),
						'MC' => __('Monaco', 'wams'),
						'MN' => __('Mongolia', 'wams'),
						'ME' => __('Montenegro', 'wams'),
						'MS' => __('Montserrat', 'wams'),
						'MA' => __('Morocco', 'wams'),
						'MZ' => __('Mozambique', 'wams'),
						'MM' => __('Myanmar', 'wams'),
						'NA' => __('Namibia', 'wams'),
						'NR' => __('Nauru', 'wams'),
						'NP' => __('Nepal', 'wams'),
						'NL' => __('Netherlands', 'wams'),
						'AN' => __('Netherlands Antilles', 'wams'),
						'NC' => __('New Caledonia', 'wams'),
						'NZ' => __('New Zealand', 'wams'),
						'NI' => __('Nicaragua', 'wams'),
						'NE' => __('Niger', 'wams'),
						'NG' => __('Nigeria', 'wams'),
						'NU' => __('Niue', 'wams'),
						'NF' => __('Norfolk Island', 'wams'),
						'MP' => __('Northern Mariana Islands', 'wams'),
						'NO' => __('Norway', 'wams'),
						'OM' => __('Oman', 'wams'),
						'PK' => __('Pakistan', 'wams'),
						'PW' => __('Palau', 'wams'),
						'PS' => __('Palestine', 'wams'),
						'PA' => __('Panama', 'wams'),
						'PG' => __('Papua New Guinea', 'wams'),
						'PY' => __('Paraguay', 'wams'),
						'PE' => __('Peru', 'wams'),
						'PH' => __('Philippines', 'wams'),
						'PN' => __('Pitcairn', 'wams'),
						'PL' => __('Poland', 'wams'),
						'PT' => __('Portugal', 'wams'),
						'PR' => __('Puerto Rico', 'wams'),
						'QA' => __('Qatar', 'wams'),
						'RE' => __('Réunion', 'wams'),
						'RO' => __('Romania', 'wams'),
						'RU' => __('Russian Federation', 'wams'),
						'RW' => __('Rwanda', 'wams'),
						'BL' => __('Saint Barthélemy', 'wams'),
						'SH' => __('Saint Helena', 'wams'),
						'KN' => __('Saint Kitts and Nevis', 'wams'),
						'LC' => __('Saint Lucia', 'wams'),
						'MF' => __('Saint Martin (French part)', 'wams'),
						'PM' => __('Saint Pierre and Miquelon', 'wams'),
						'VC' => __('Saint Vincent and the Grenadines', 'wams'),
						'WS' => __('Samoa', 'wams'),
						'SM' => __('San Marino', 'wams'),
						'ST' => __('Sao Tome and Principe', 'wams'),
						'SA' => __('Saudi Arabia', 'wams'),
						'SN' => __('Senegal', 'wams'),
						'RS' => __('Serbia', 'wams'),
						'SC' => __('Seychelles', 'wams'),
						'SL' => __('Sierra Leone', 'wams'),
						'SG' => __('Singapore', 'wams'),
						'SK' => __('Slovakia', 'wams'),
						'SI' => __('Slovenia', 'wams'),
						'SB' => __('Solomon Islands', 'wams'),
						'SO' => __('Somalia', 'wams'),
						'ZA' => __('South Africa', 'wams'),
						'GS' => __('South Georgia and the South Sandwich Islands', 'wams'),
						'SS' => __('South Sudan', 'wams'),
						'ES' => __('Spain', 'wams'),
						'LK' => __('Sri Lanka', 'wams'),
						'SD' => __('Sudan', 'wams'),
						'SR' => __('Suriname', 'wams'),
						'SJ' => __('Svalbard and Jan Mayen', 'wams'),
						'SZ' => __('Eswatini', 'wams'),
						'SE' => __('Sweden', 'wams'),
						'CH' => __('Switzerland', 'wams'),
						'SY' => __('Syrian Arab Republic', 'wams'),
						'TW' => __('Taiwan, Province of China', 'wams'),
						'TJ' => __('Tajikistan', 'wams'),
						'TZ' => __('Tanzania, United Republic of', 'wams'),
						'TH' => __('Thailand', 'wams'),
						'TL' => __('Timor-Leste', 'wams'),
						'TG' => __('Togo', 'wams'),
						'TK' => __('Tokelau', 'wams'),
						'TO' => __('Tonga', 'wams'),
						'TT' => __('Trinidad and Tobago', 'wams'),
						'TN' => __('Tunisia', 'wams'),
						'TR' => __('Turkey', 'wams'),
						'TM' => __('Turkmenistan', 'wams'),
						'TC' => __('Turks and Caicos Islands', 'wams'),
						'TV' => __('Tuvalu', 'wams'),
						'UG' => __('Uganda', 'wams'),
						'UA' => __('Ukraine', 'wams'),
						'AE' => __('United Arab Emirates', 'wams'),
						'GB' => __('United Kingdom', 'wams'),
						'US' => __('United States', 'wams'),
						'UM' => __('United States Minor Outlying Islands', 'wams'),
						'UY' => __('Uruguay', 'wams'),
						'UZ' => __('Uzbekistan', 'wams'),
						'VU' => __('Vanuatu', 'wams'),
						'VE' => __('Venezuela, Bolivarian Republic of', 'wams'),
						'VN' => __('Viet Nam', 'wams'),
						'VG' => __('Virgin Islands, British', 'wams'),
						'VI' => __('Virgin Islands, U.S.', 'wams'),
						'WF' => __('Wallis and Futuna', 'wams'),
						'EH' => __('Western Sahara', 'wams'),
						'YE' => __('Yemen', 'wams'),
						'ZM' => __('Zambia', 'wams'),
						'ZW' => __('Zimbabwe', 'wams'),
					);
					break;
			}
			return $array;
		}
	}
}
