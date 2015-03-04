<?php
/*
---------------------------------INCLUDE THIS SECTION IN ANY MODIFICATION OR REDISTRIBUTION----------------------------------
Project: Form Builder Class
Author: Andrew Porterfield
Original Release Date: 2009-04-24
Latest Release Date: 2009-11-05
License: GPL - for more information visit http://www.gnu.org/licenses/quick-guide-gplv3.html
Current Version: 0.9.3
---------------------------------INCLUDE THIS SECTION IN ANY MODIFICATION OR REDISTRIBUTION----------------------------------
*/

class HelperBase {
	/*This class provides two methods - setAttributes and debug - that can be used for all classes that extend this class.*/
	function setAttributes($params) {
		if(!empty($params) && is_array($params))
		{
			/*Loop through and get accessible class variables.*/
			$objArr = array();
			foreach($this as $key => $value)
				$objArr[$key] = $value;

			foreach($params as $key => $value)
			{
				if(array_key_exists($key, $objArr))
				{
					if(is_array($this->$key) && !empty($this->$key))
					{
						/*Using array_merge prevents any default values from being overwritten.*/
						$this->$key = array_merge($this->$key, $value);
					}	
					else
						$this->$key = $value;
				}
				elseif(array_key_exists("attributes", $objArr))
					$this->attributes[$key] = $value;
			}
			unset($objArr);
		}
	}

	/*Used for development/testing.*/
	function debug()
	{
		echo "<pre>";
			print_r($this);
		echo "</pre>";
	}
}

class FormHelper extends HelperBase { 
	/*Variables that can be set through the setAttributes function on the base class.*/
	protected $attributes;				/*HTML attributes attached to <form> tag.*/
	protected $tableAttributes;			/*HTML attributes attached to <table> tag.*/
	protected $tdAttributes;			/*HTML attributes attached to <td> tag.*/
	protected $labelAttributes;			/*HTML attributes attached to <div> tag.*/
	protected $requiredAttributes;		/*HTML attributes attached to <span> tag.*/
	protected $jqueryPath;				/*Allows jquery directory's location to be identified.*/
	protected $googleMapsAPIKey;		/*Required for using latlng/map field type.*/
	protected $map;						/*Unrelated to latlng/map field type.  Used to control table structure.*/
	protected $returnUrl;				/*Only used when doign php validation via the checkForm function.*/
	protected $ajax;					/*Activate ajax form submission.*/
	protected $ajaxType;				/*Specify form submission as get/post.*/
	protected $ajaxUrl;					/*Where to send ajax submission.*/
	protected $ajaxCallback;			/*Optional function to call after successful ajax form submission.*/
	protected $ajaxDataType;			/*Defaults to text.  Options include xml, html, script, json, jsonp, and text.  View details at http://docs.jquery.com/Ajax/jQuery.ajax#options*/
	protected $tooltipIcon;				/*Overrides default tooltip icon.*/
	protected $tooltipBorderColor;		/*Overrides default tooltip border color.*/
	protected $preventJQueryLoad;		/*Prevents jQuery js file from being loaded twice.*/
	protected $preventJQueryUILoad;		/*Prevents jQuery UI js file from being loaded twice.*/
	protected $preventQTipLoad;			/*Prevents qTip js file from being loaded twice.*/
	protected $preventGoogleMapsLoad;	/*Prevents Google Maps js file from being loaded twice.*/
	protected $noLabels;				/*Prevents labels from being rendered on checkboxes and radio buttons.*/
	protected $noAutoFocus;				/*Prevents auto-focus feature..*/
	protected $tinymcePath;				/*Allows tiny_mce directory's location to be identified.*/

	/*Variables that can only be set inside this class.*/
	private $elements;
	private $buttons;
	private $jqueryDateArr;
	private $jqueryDateRangeArr;
	private $jquerySortArr;
	private $jquerySliderArr;
	private $jqueryCheckSort;
	private $latlngArr;
	private $checkform;
	private $allowedFields;				/*Controls what attributes can be attached to various html elements.*/
	private $stateArr;
	private $countryArr;
	private $referenceValues;			/*Associative array of values to pre-fill form fields.*/
	private $tooltipArr;
	private $focusElement;
	private $includeTinyMce;			/*Will be set if a field of type webeditor is detected.*/
	private $tinymceIDArr;				/*Ensurse that each webeditor form element has a unique identifier.*/
	private $captchaNameArr;				/*Ensurse that each captcha form element has a unique identifier.*/

	public function __construct() {
		/*Provide default values for class variables.*/
		$this->attributes = array(
			"name" => "formclass_" . rand(0, 999),
			"method" => "post",
			"action" => basename($_SERVER["SCRIPT_NAME"]),
			"style" => "padding: 0; margin: 0;"
		);
		$this->tableAttributes = array(
			"cellpadding" => "4",
			"cellspacing" => "0",
			"border" => "0"
		);
		$this->tdAttributes = array(
			"valign" => "top",
			"align" => "left"
		);
		$this->requiredAttributes = array(
			"style" => "color: #990000;"
		);
		$this->jqueryPath = "jquery";
		$this->tinymcePath = "tiny_mce";
		$this->captchaPath = "animatedcaptcha";
		/*This array prevents junk from being inserted into the form's HTML.  If you find that an attributes you need to use is not included
		in this list, feel free to customize to fit your needs.*/
		$this->allowedFields = array(
			"form" => array("method", "action", "enctype", "onsubmit", "id", "class", "name"),
			"table" => array("cellpadding", "cellspacing", "border", "style", "id", "class", "name", "align", "width"),
			"td" => array("id", "name", "valign", "align", "style", "id", "class", "width"),
			"div" => array("id", "name", "valign", "align", "style", "id", "class"),
			"span" => array("id", "name", "valign", "align", "style", "id", "class"),
			"hidden" => array("id", "name", "value", "type"),
			"text" => array("id", "name", "value", "type", "class", "style", "onclick", "onkeyup", "onblur", "maxlength", "size"),
			"file" => array("id", "name", "value", "type", "class", "style", "onclick", "onkeyup", "onblur", "maxlength", "size"),
			"textarea" => array("id", "name", "class", "style", "onclick", "onkeyup", "maxlength", "onblur", "size", "rows", "cols"),
			"select" => array("id", "name", "class", "style", "onclick", "onchange", "onblur", "size"),
			"radio" => array("name", "style", "class", "onclick", "type"),
			"checkbox" => array("name", "style", "class", "onclick", "type"),
			"checksort" => array("style", "class"),
			"date" => array("id", "name", "value", "type", "class", "style", "onclick", "onkeyup", "onblur", "maxlength", "size"),
			"button" => array("name", "value", "type", "id", "onclick", "class", "style"),
			"a" => array("id", "name", "href", "class", "style"),
			"latlng" => array("id", "name", "type", "class", "style", "onclick", "onkeyup", "maxlength", "size")
		);

		$this->ajaxType = "post";
		$this->ajaxUrl = basename($_SERVER["SCRIPT_NAME"]);
		$this->ajaxDataType = "text";
	}

	/*Creates new element object instances and attaches them to the form object.  This function is private and can only be called inside this class.*/
	private function attachElement($params)
	{
		$ele = new element();
		$ele->setAttributes($params);
		$eleType = &$ele->attributes["type"];

		if($eleType == "state")
		{
			/*This section prevents the stateArr from being generated for each form and/or multiple state field types per form.*/
			$eleType = "select";
			if(empty($this->stateArr))
			{
				$this->stateArr = array(
					array("value" => "", "text" => "--Select a State/Province--"),
					array("value" => "AL", "text" => "Alabama"),
					array("value" => "AK", "text" => "Alaska"),
					array("value" => "AZ", "text" => "Arizona"),
					array("value" => "AR", "text" => "Arkansas"),
					array("value" => "CA", "text" => "California"),
					array("value" => "CO", "text" => "Colorado"),
					array("value" => "CT", "text" => "Connecticut"),
					array("value" => "DE", "text" => "Delaware"),
					array("value" => "DC", "text" => "District of Columbia"),
					array("value" => "FL", "text" => "Florida"),
					array("value" => "GA", "text" => "Georgia"),
					array("value" => "HI", "text" => "Hawaii"),
					array("value" => "ID", "text" => "Idaho"),
					array("value" => "IL", "text" => "Illinois"),
					array("value" => "IN", "text" => "Indiana"),
					array("value" => "IA", "text" => "Iowa"),
					array("value" => "KS", "text" => "Kansas"),
					array("value" => "KY", "text" => "Kentucky"),
					array("value" => "LA", "text" => "Louisiana"),
					array("value" => "ME", "text" => "Maine"),
					array("value" => "MD", "text" => "Maryland"),
					array("value" => "MA", "text" => "Massachusetts"),
					array("value" => "MI", "text" => "Michigan"),
					array("value" => "MN", "text" => "Minnesota"),
					array("value" => "MS", "text" => "Mississippi"),
					array("value" => "MO", "text" => "Missouri"),
					array("value" => "MT", "text" => "Montana"),
					array("value" => "NE", "text" => "Nebraska"),
					array("value" => "NV", "text" => "Nevada"),
					array("value" => "NH", "text" => "New Hampshire"),
					array("value" => "NJ", "text" => "New Jersey"),
					array("value" => "NM", "text" => "New Mexico"),
					array("value" => "NY", "text" => "New York"),
					array("value" => "NC", "text" => "North Carolina"),
					array("value" => "ND", "text" => "North Dakota"),
					array("value" => "OH", "text" => "Ohio"),
					array("value" => "OK", "text" => "Oklahoma"),
					array("value" => "OR", "text" => "Oregon"),
					array("value" => "PA", "text" => "Pennsylvania"),
					array("value" => "RI", "text" => "Rhode Island"),
					array("value" => "SC", "text" => "South Carolina"),
					array("value" => "SD", "text" => "South Dakota"),
					array("value" => "TN", "text" => "Tennessee"),
					array("value" => "TX", "text" => "Texas"),
					array("value" => "UT", "text" => "Utah"),
					array("value" => "VT", "text" => "Vermont"),
					array("value" => "VA", "text" => "Virginia"),
					array("value" => "WA", "text" => "Washington"),
					array("value" => "WV", "text" => "West Virginia"),
					array("value" => "WI", "text" => "Wisconsin"),
					array("value" => "WY", "text" => "Wyoming"),
					array("value" => "", "text" => ""),
					array("value" => "", "text" => "-- Canadian Province--"),
					array("value" => "AB", "text" => "Alberta"),
					array("value" => "BC", "text" => "British Columbia"),
					array("value" => "MB", "text" => "Manitoba"),
					array("value" => "NB", "text" => "New Brunswick"),
					array("value" => "NL", "text" => "Newfoundland and Labrador"),
					array("value" => "NS", "text" => "Nova Scotia"),
					array("value" => "NT", "text" => "Northwest Territories"),
					array("value" => "NU", "text" => "Nunavut"),
					array("value" => "ON", "text" => "Ontario"),
					array("value" => "PE", "text" => "Prince Edward Island"),
					array("value" => "QC", "text" => "Qu&#233;bec"),
					array("value" => "SK", "text" => "Saskatchewan"),
					array("value" => "YT", "text" => "Yukon"),
					array("value" => "", "text" => ""),
					array("value" => "", "text" => "-- US Territories--"),
					array("value" => "AS", "text" => "American Samoa"),
					array("value" => "FM", "text" => "Federated States of Micronesia"),
					array("value" => "GU", "text" => "Guam"),
					array("value" => "MH", "text" => "Marshall Islands"),
					array("value" => "PW", "text" => "Palau"),
					array("value" => "PR", "text" => "Puerto Rico"),
					array("value" => "VI", "text" => "Virgin Islands")
				);
			}
			$ele->options = array();
			$stateSize = sizeof($this->stateArr);
			for($s = 0; $s < $stateSize; ++$s)
			{
				$opt = new option();
				$opt->setAttributes($this->stateArr[$s]);
				$ele->options[] = $opt;
			}
		}	
		elseif($eleType == "country")
		{
			/*This section prevents the countryArr from being generated for each form and/or multiple country field types per form.*/
			$eleType = "select";
			if(empty($this->countryArr))
			{
				$this->countryArr = array(
					array("value" => "", "text" => "--Select a Country--"),
					array("value" => "US", "text" => "United States"),
					array("value" => "AF", "text" => "Afghanistan"),
					array("value" => "AL", "text" => "Albania"),
					array("value" => "DZ", "text" => "Algeria"),
					array("value" => "AS", "text" => "American Samoa"),
					array("value" => "AD", "text" => "Andorra"),
					array("value" => "AO", "text" => "Angola"),
					array("value" => "AI", "text" => "Anguilla"),
					array("value" => "AG", "text" => "Antigua and Barbuda"),
					array("value" => "AR", "text" => "Argentina"),
					array("value" => "AM", "text" => "Armenia"),
					array("value" => "AW", "text" => "Aruba"),
					array("value" => "AU", "text" => "Australia"),
					array("value" => "AT", "text" => "Austria"),
					array("value" => "AZ", "text" => "Azerbaijan"),
					array("value" => "BS", "text" => "Bahamas"),
					array("value" => "BH", "text" => "Bahrain"),
					array("value" => "BD", "text" => "Bangladesh"),
					array("value" => "BB", "text" => "Barbados"),
					array("value" => "BY", "text" => "Belarus"),
					array("value" => "BE", "text" => "Belgium"),
					array("value" => "BZ", "text" => "Belize"),
					array("value" => "BJ", "text" => "Benin"),
					array("value" => "BM", "text" => "Bermuda"),
					array("value" => "BT", "text" => "Bhutan"),
					array("value" => "BO", "text" => "Bolivia"),
					array("value" => "BA", "text" => "Bosnia and Herzegowina"),
					array("value" => "BW", "text" => "Botswana"),
					array("value" => "BR", "text" => "Brazil"),
					array("value" => "IO", "text" => "British Indian Ocean Territory"),
					array("value" => "BN", "text" => "Brunei Darussalam"),
					array("value" => "BG", "text" => "Bulgaria"),
					array("value" => "BF", "text" => "Burkina Faso"),
					array("value" => "BI", "text" => "Burundi"),
					array("value" => "KH", "text" => "Cambodia"),
					array("value" => "CM", "text" => "Cameroon"),
					array("value" => "CA", "text" => "Canada"),
					array("value" => "CV", "text" => "Cape Verde"),
					array("value" => "KY", "text" => "Cayman Islands"),
					array("value" => "CF", "text" => "Central African Republic"),
					array("value" => "TD", "text" => "Chad"),
					array("value" => "CL", "text" => "Chile"),
					array("value" => "CN", "text" => "China"),
					array("value" => "CO", "text" => "Colombia"),
					array("value" => "CG", "text" => "Congo"),
					array("value" => "CK", "text" => "Cook Islands"),
					array("value" => "CR", "text" => "Costa Rica"),
					array("value" => "CI", "text" => "Cote d'Ivoire"),
					array("value" => "HR", "text" => "Croatia"),
					array("value" => "CY", "text" => "Cyprus"),
					array("value" => "CZ", "text" => "Czech Republic"),
					array("value" => "DK", "text" => "Denmark"),
					array("value" => "DJ", "text" => "Djibouti"),
					array("value" => "DM", "text" => "Dominica"),
					array("value" => "DO", "text" => "Dominican Republic"),
					array("value" => "EC", "text" => "Ecuador"),
					array("value" => "EG", "text" => "Egypt"),
					array("value" => "SV", "text" => "El Salvador"),
					array("value" => "GQ", "text" => "Equatorial Guinea"),
					array("value" => "ER", "text" => "Eritrea"),
					array("value" => "EE", "text" => "Estonia"),
					array("value" => "ET", "text" => "Ethiopia"),
					array("value" => "FO", "text" => "Faroe Islands"),
					array("value" => "FJ", "text" => "Fiji"),
					array("value" => "FI", "text" => "Finland"),
					array("value" => "FR", "text" => "France"),
					array("value" => "GF", "text" => "French Guiana"),
					array("value" => "PF", "text" => "French Polynesia"),
					array("value" => "GA", "text" => "Gabon"),
					array("value" => "GM", "text" => "Gambia"),
					array("value" => "GE", "text" => "Georgia"),
					array("value" => "DE", "text" => "Germany"),
					array("value" => "GH", "text" => "Ghana"),
					array("value" => "GI", "text" => "Gibraltar"),
					array("value" => "GR", "text" => "Greece"),
					array("value" => "GL", "text" => "Greenland"),
					array("value" => "GD", "text" => "Grenada"),
					array("value" => "GP", "text" => "Guadeloupe"),
					array("value" => "GU", "text" => "Guam"),
					array("value" => "GT", "text" => "Guatemala"),
					array("value" => "GN", "text" => "Guinea"),
					array("value" => "GW", "text" => "Guinea-Bissau"),
					array("value" => "GY", "text" => "Guyana"),
					array("value" => "HT", "text" => "Haiti"),
					array("value" => "HM", "text" => "Heard Island And Mcdonald Islands"),
					array("value" => "HK", "text" => "Hong Kong"),
					array("value" => "HU", "text" => "Hungary"),
					array("value" => "IS", "text" => "Iceland"),
					array("value" => "IN", "text" => "India"),
					array("value" => "ID", "text" => "Indonesia"),
					array("value" => "IR", "text" => "Iran, Islamic Republic Of"),
					array("value" => "IL", "text" => "Israel"),
					array("value" => "IT", "text" => "Italy"),
					array("value" => "JM", "text" => "Jamaica"),
					array("value" => "JP", "text" => "Japan"),
					array("value" => "JO", "text" => "Jordan"),
					array("value" => "KZ", "text" => "Kazakhstan"),
					array("value" => "KE", "text" => "Kenya"),
					array("value" => "KI", "text" => "Kiribati"),
					array("value" => "KP", "text" => "Korea, Democratic People's Republic Of"),
					array("value" => "KW", "text" => "Kuwait"),
					array("value" => "KG", "text" => "Kyrgyzstan"),
					array("value" => "LA", "text" => "Lao People's Democratic Republic"),
					array("value" => "LV", "text" => "Latvia"),
					array("value" => "LB", "text" => "Lebanon"),
					array("value" => "LS", "text" => "Lesotho"),
					array("value" => "LR", "text" => "Liberia"),
					array("value" => "LI", "text" => "Liechtenstein"),
					array("value" => "LT", "text" => "Lithuania"),
					array("value" => "LU", "text" => "Luxembourg"),
					array("value" => "MO", "text" => "Macau"),
					array("value" => "MK", "text" => "Macedonia, The Former Yugoslav Republic Of"),
					array("value" => "MG", "text" => "Madagascar"),
					array("value" => "MW", "text" => "Malawi"),
					array("value" => "MY", "text" => "Malaysia"),
					array("value" => "MV", "text" => "Maldives"),
					array("value" => "ML", "text" => "Mali"),
					array("value" => "MT", "text" => "Malta"),
					array("value" => "MH", "text" => "Marshall Islands"),
					array("value" => "MQ", "text" => "Martinique"),
					array("value" => "MR", "text" => "Mauritania"),
					array("value" => "MU", "text" => "Mauritius"),
					array("value" => "MX", "text" => "Mexico"),
					array("value" => "FM", "text" => "Micronesia, Federated States Of"),
					array("value" => "MD", "text" => "Moldova, Republic Of"),
					array("value" => "MC", "text" => "Monaco"),
					array("value" => "MN", "text" => "Mongolia"),
					array("value" => "MS", "text" => "Montserrat"),
					array("value" => "MA", "text" => "Morocco"),
					array("value" => "MZ", "text" => "Mozambique"),
					array("value" => "NA", "text" => "Namibia"),
					array("value" => "NP", "text" => "Nepal"),
					array("value" => "NL", "text" => "Netherlands"),
					array("value" => "AN", "text" => "Netherlands Antilles"),
					array("value" => "NC", "text" => "New Caledonia"),
					array("value" => "NZ", "text" => "New Zealand"),
					array("value" => "NI", "text" => "Nicaragua"),
					array("value" => "NE", "text" => "Niger"),
					array("value" => "NG", "text" => "Nigeria"),
					array("value" => "NF", "text" => "Norfolk Island"),
					array("value" => "MP", "text" => "Northern Mariana Islands"),
					array("value" => "NO", "text" => "Norway"),
					array("value" => "OM", "text" => "Oman"),
					array("value" => "PK", "text" => "Pakistan"),
					array("value" => "PW", "text" => "Palau"),
					array("value" => "PA", "text" => "Panama"),
					array("value" => "PG", "text" => "Papua New Guinea"),
					array("value" => "PY", "text" => "Paraguay"),
					array("value" => "PE", "text" => "Peru"),
					array("value" => "PH", "text" => "Philippines"),
					array("value" => "PL", "text" => "Poland"),
					array("value" => "PT", "text" => "Portugal"),
					array("value" => "PR", "text" => "Puerto Rico"),
					array("value" => "QA", "text" => "Qatar"),
					array("value" => "RE", "text" => "Reunion"),
					array("value" => "RO", "text" => "Romania"),
					array("value" => "RU", "text" => "Russian Federation"),
					array("value" => "RW", "text" => "Rwanda"),
					array("value" => "KN", "text" => "Saint Kitts and Nevis"),
					array("value" => "LC", "text" => "Saint Lucia"),
					array("value" => "VC", "text" => "Saint Vincent and the Grenadines"),
					array("value" => "WS", "text" => "Samoa"),
					array("value" => "SM", "text" => "San Marino"),
					array("value" => "SA", "text" => "Saudi Arabia"),
					array("value" => "SN", "text" => "Senegal"),
					array("value" => "SC", "text" => "Seychelles"),
					array("value" => "SL", "text" => "Sierra Leone"),
					array("value" => "SG", "text" => "Singapore"),
					array("value" => "SK", "text" => "Slovakia"),
					array("value" => "SI", "text" => "Slovenia"),
					array("value" => "SB", "text" => "Solomon Islands"),
					array("value" => "SO", "text" => "Somalia"),
					array("value" => "ZA", "text" => "South Africa"),
					array("value" => "ES", "text" => "Spain"),
					array("value" => "LK", "text" => "Sri Lanka"),
					array("value" => "SD", "text" => "Sudan"),
					array("value" => "SR", "text" => "Suriname"),
					array("value" => "SZ", "text" => "Swaziland"),
					array("value" => "SE", "text" => "Sweden"),
					array("value" => "CH", "text" => "Switzerland"),
					array("value" => "SY", "text" => "Syrian Arab Republic"),
					array("value" => "TW", "text" => "Taiwan, Province Of China"),
					array("value" => "TJ", "text" => "Tajikistan"),
					array("value" => "TZ", "text" => "Tanzania, United Republic Of"),
					array("value" => "TH", "text" => "Thailand"),
					array("value" => "TG", "text" => "Togo"),
					array("value" => "TO", "text" => "Tonga"),
					array("value" => "TT", "text" => "Trinidad and Tobago"),
					array("value" => "TN", "text" => "Tunisia"),
					array("value" => "TR", "text" => "Turkey"),
					array("value" => "TM", "text" => "Turkmenistan"),
					array("value" => "TC", "text" => "Turks and Caicos Islands"),
					array("value" => "TV", "text" => "Tuvalu"),
					array("value" => "UG", "text" => "Uganda"),
					array("value" => "UA", "text" => "Ukraine"),
					array("value" => "AE", "text" => "United Arab Emirates"),
					array("value" => "GB", "text" => "United Kingdom"),
					array("value" => "UY", "text" => "Uruguay"),
					array("value" => "UZ", "text" => "Uzbekistan"),
					array("value" => "VU", "text" => "Vanuatu"),
					array("value" => "VE", "text" => "Venezuela"),
					array("value" => "VN", "text" => "Vietnam"),
					array("value" => "VG", "text" => "Virgin Islands (British)"),
					array("value" => "VI", "text" => "Virgin Islands (U.S.)"),
					array("value" => "WF", "text" => "Wallis and Futuna Islands"),
					array("value" => "EH", "text" => "Western Sahara"),
					array("value" => "YE", "text" => "Yemen"),
					array("value" => "YU", "text" => "Yugoslavia"),
					array("value" => "ZM", "text" => "Zambia"),
					array("value" => "ZR", "text" => "Zaire"),
					array("value" => "ZW", "text" => "Zimbabwe")
				);
			}
			$ele->options = array();
			$countrySize = sizeof($this->countryArr);
			for($s = 0; $s < $countrySize; ++$s)
			{
				$opt = new option();
				$opt->setAttributes($this->countryArr[$s]);
				$ele->options[] = $opt;
			}
		}
		elseif($eleType == "yesno")
		{
			$eleType = "radio";
			$ele->options = array();
			$opt = new option();
			$opt->setAttributes(array("value" => "1", "text" => "Yes"));
			$ele->options[] = $opt;
			$opt = new option();
			$opt->setAttributes(array("value" => "0", "text" => "No"));
			$ele->options[] = $opt;
		}
		elseif($eleType == "truefalse")
		{
			$eleType = "radio";
			$ele->options = array();
			$opt = new option();
			$opt->setAttributes(array("value" => "1", "text" => "True"));
			$ele->options[] = $opt;
			$opt = new option();
			$opt->setAttributes(array("value" => "0", "text" => "False"));
			$ele->options[] = $opt;
		}
		else
		{
			/*Various form types (select, radio, check, sort) use the options parameter to handle multiple choice elements.*/
			if(array_key_exists("options", $params) && is_array($params["options"]))
			{
				$ele->options = array();
				/*If the options array is numeric, assign the key and text to each value.*/
				if(array_values($params["options"]) === $params["options"])
				{
					foreach($params["options"] as $key => $value)
					{
						$opt = new option();
						$opt->setAttributes(array("value" => $value, "text" => $value));
						$ele->options[] = $opt;
					}
				}
				/*If the options array is associative, assign the key and text to each key/value pair.*/
				else
				{
					foreach($params["options"] as $key => $value)
					{
						$opt = new option();
						$opt->setAttributes(array("value" => $key, "text" => $value));
						$ele->options[] = $opt;
					}
				}
			}

			/*If there is a file field type in the form, make sure that the encytype is set accordingly.*/
			if($eleType == "file")
				$this->attributes["enctype"] = "multipart/form-data";

			/*Ensures tinymce code will be included if an element of type webeditor is detected.*/
			if($eleType == "webeditor")
				$this->includeTinyMce = 1;
		}

		/*If there is a required field type in the form, make sure javascript error checking is enabled.*/
		if(!empty($ele->required) && empty($this->checkform))
			$this->checkform = 1;
		
		$this->elements[] = $ele;
	}
	
	/*-------------------------------------------START: HOW USERS CAN ADD FORM FIELDS--------------------------------------------*/

	/*addElements allows users to add multiple form elements by passing a multi-dimensional array.*/
	public function addElements($params)
	{
		$paramSize = sizeof($params);
		for($i = 0; $i < $paramSize; ++$i)
			$this->attachElement($params[$i]);
	}

	/*addElement allows users to add a single form element by passing an array.*/
	public function addElement($label, $name, $type="", $value="", $additionalParams="")
	{
		$params = array("label" => $label, "name" => $name);
		if(!empty($type))
			$params["type"] = $type;
		$params["value"] = $value;
			
		/*Commonly used attributes such as name, type, and value exist as parameters in the function.  All other attributes
		that need to be included should be passed in the additionalParams field.  This field should exist as an associative
		array with the key being the attribute's name.  Examples of attributes passed in the additionalParams field include
		style, class, and onkeyup.*/	
		if(!empty($additionalParams) && is_array($additionalParams))
		{
			foreach($additionalParams as $key => $value)
				$params[$key] = $value;
		}
		$this->attachElement($params);
	}

	/*The remaining function are shortcuts for adding each supported form field.*/
	public function addHidden($name, $value="", $additionalParams="") {
		$this->addElement("", $name, "hidden", $value, $additionalParams);
	}
	public function addTextbox($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "text", $value, $additionalParams);
	}
	public function addTextarea($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "textarea", $value, $additionalParams);
	}
	public function addWebEditor($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "webeditor", $value, $additionalParams);
	}
	public function addPassword($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "password", $value, $additionalParams);
	}
	public function addFile($label, $name, $additionalParams="") {
		$this->addElement($label, $name, "file", "", $additionalParams);
	}
	public function addDate($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "date", $value, $additionalParams);
	}
	public function addDateRange($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "daterange", $value, $additionalParams);
	}
	public function addState($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "state", $value, $additionalParams);
	}
	public function addCountry($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "country", $value, $additionalParams);
	}
	public function addYesNo($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "yesno", $value, $additionalParams);
	}
	public function addTrueFalse($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "truefalse", $value, $additionalParams);
	}
	/*This function is included for backwards compatability.*/
	public function addSelectbox($label, $name, $value="", $options="", $additionalParams="") {
		$this->addSelect($label, $name, $value, $options, $additionalParams);
	}
	public function addSelect($label, $name, $value="", $options="", $additionalParams="") {
		if(!is_array($additionalParams))
			$additionalParams = array();
		$additionalParams["options"] = $options;	
		$this->addElement($label, $name, "select", $value, $additionalParams);
	}
	public function addRadio($label, $name, $value="", $options="", $additionalParams="") {
		if(!is_array($additionalParams))
			$additionalParams = array();
		$additionalParams["options"] = $options;	
		$this->addElement($label, $name, "radio", $value, $additionalParams);
	}
	public function addCheckbox($label, $name, $value="", $options="", $additionalParams="") {
		if(!is_array($additionalParams))
			$additionalParams = array();
		$additionalParams["options"] = $options;	
		$this->addElement($label, $name, "checkbox", $value, $additionalParams);
	}
	public function addSort($label, $name, $options="", $additionalParams="") {
		if(!is_array($additionalParams))
			$additionalParams = array();
		$additionalParams["options"] = $options;	
		$this->addElement($label, $name, "sort", "", $additionalParams);
	}
	public function addLatLng($label, $name, $value="", $additionalParams="") {
		$this->addMap($label, $name, $value, $additionalParams);
	}
	/*This function is included for backwards compatability.*/
	public function addMap($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "latlng", $value, $additionalParams);
	}
	public function addCheckSort($label, $name, $value="", $options="", $additionalParams="") {
		if(!is_array($additionalParams))
			$additionalParams = array();
		$additionalParams["options"] = $options;	
		$this->addElement($label, $name, "checksort", $value, $additionalParams);
	}
	public function addCaptcha($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "captcha", $value, $additionalParams);
	}	
	public function addSlider($label, $name, $value="", $additionalParams="") {
		$this->addElement($label, $name, "slider", $value, $additionalParams);
	}
	/*-------------------------------------------END: HOW USERS CAN ADD FORM FIELDS--------------------------------------------*/

	/*This function can be called to clear all attached element object instances from the form - beneficial when using the elementsToString function.*/
	public function clearElements() {
		$this->elements = array();
	}

	/*This function can be called to clear all attached button object instances from the form.*/
	public function clearButtons() {
		$this->buttons = array();
	}

	/*This function creates new button object instances and attaches them to the form.  It is private and can only be used inside this class.*/
	private function attachButton($params)
	{
		$button = new button();
		$button->setAttributes($params);
		$this->buttons[] = $button;
	}

	/*This function allows users to add multiple button object instances to the form by passing a multi-dimensional array.*/
	public function addButtons($params)
	{
		$paramSize = sizeof($params);
		for($i = 0; $i < $paramSize; ++$i)
			$this->attachButton($params[$i]);
	}

	/*This function allows users to add a single button object instance to the form by passing an array.*/
	function addButton($value="Submit", $type="submit", $additionalParams="")
	{
		$params = array("value" => $value, "type" => $type);

		/*The additionalParams performs a similar role as in the addElement function.  For more information, please read to description
		of this field in the addElement function.  Commonly used attributes included for additionalParams in this function include
		onclick.*/
		if(!empty($additionalParams) && is_array($additionalParams))
		{
			foreach($additionalParams as $key => $value)
				$params[$key] = $value;
		}
		$this->attachButton($params);
	}

	/*This function renders the form's HTML.*/
	public function render()
	{
		ob_start();
		echo("\n<form");
		if(!empty($this->attributes) && is_array($this->attributes))
		{
			$tmpAllowFieldArr = $this->allowedFields["form"];
			foreach($this->attributes as $key => $value)
			{
				if($key == "onsubmit" && (!empty($this->checkform) || !empty($this->ajax)))
					continue;
				if(in_array($key, $tmpAllowFieldArr))
					echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
			}	
		}
			
		if(!empty($this->checkform) || !empty($this->ajax))	
			echo ' onsubmit="return formhandler_', $this->attributes["name"], '(this);"';
		echo(">\n");

		$elementSize = sizeof($this->elements);
		for($i = 0; $i < $elementSize; ++$i)
		{
			$ele = $this->elements[$i];
			if($ele->attributes["type"] == "hidden")
			{
				echo "<input";
				if(!empty($ele->attributes) && is_array($ele->attributes))
				{
					$tmpAllowFieldArr = $this->allowedFields["hidden"];
					foreach($ele->attributes as $key => $value)
					{
						if(in_array($key, $tmpAllowFieldArr))
							echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
					}		
				}
				echo "/>\n";
			}
		}	

		echo("<table");
		if(!empty($this->tableAttributes) && is_array($this->tableAttributes))
		{
			$tmpAllowFieldArr = $this->allowedFields["table"];
			foreach($this->tableAttributes as $key => $value)
			{
				if(in_array($key, $tmpAllowFieldArr))
					echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
			}		
		}
		echo(">\n");

		/*Render the elements by calling elementsToString function with the includeTable tags field set to false.  There is no need
		to render the table tag b/c we have just done that above.*/
		echo($this->elementsToString(false));

		/*If there are buttons included, render those to the screen now.*/
		if(!empty($this->buttons))
		{
			echo "\t", '<tr><td align="right"';
			if(!empty($this->tdAttributes) && is_array($this->tdAttributes))
			{
				$tmpAllowFieldArr = $this->allowedFields["td"];
				foreach($this->tdAttributes as $key => $value)
				{
					if($key != "align" && in_array($key, $tmpAllowFieldArr))
						echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
				}		
			}
			echo(">\n");
			$buttonSize = sizeof($this->buttons);
			for($i = 0; $i < $buttonSize; ++$i)
			{
				if(!empty($this->buttons[$i]->wrapLink))
				{
					echo("\t\t<a");
					if(!empty($this->buttons[$i]->linkAttributes) && is_array($this->buttons[$i]->linkAttributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["a"];
						foreach($this->buttons[$i]->linkAttributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
						}		
					}
					echo(">");
				}
				else
					echo("\t");

				if(!empty($this->buttons[$i]->phpFunction))
				{
					$execStr = $this->buttons[$i]->phpFunction . "(";
					if(!empty($this->buttons[$i]->phpParams))
					{
						if(is_array($this->buttons[$i]->phpParams))
						{
							$paramSize = sizeof($this->buttons[$i]->phpParams);
							for($p = 0; $p < $paramSize; ++$p)
							{
								if($p != 0)
									$execStr .= ",";

								if(is_string($this->buttons[$i]->phpParams[$p]))	
									$execStr .= '"' . $this->buttons[$i]->phpParams[$p] . '"';
								else	
									$execStr .= $this->buttons[$i]->phpParams[$p];	
							}
						}
						else
							$execStr .= $this->buttons[$i]->phpParams;
					}
					$execStr .= ");";
					echo(eval("return " . $execStr));
				}
				else
				{
					if(empty($this->buttons[$i]->wrapLink))
						echo("\t");
					echo("<input");
					if(!empty($this->buttons[$i]->attributes) && is_array($this->buttons[$i]->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["button"];
						foreach($this->buttons[$i]->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								echo ' ', $key, '="', str_replace('"', '&quot;', $value), '"';
						}		
					}
					echo("/>");
				}

				if(!empty($this->buttons[$i]->wrapLink))
					echo("</a>");
				
				echo("\n");
			}
			echo("\t</td></tr>\n");
		}
		echo("</table>\n");

		echo("</form>\n\n");

		/*
		If there are any required fields in the form or if this form is setup to utilize ajax, build a javascript 
		function for performing form validation before submission and/or for building and submitting a data string through ajax.
		*/
		if(!empty($this->checkform) || !empty($this->ajax))
		{
			echo '<script language="javascript">';
			echo "\n\tfunction formhandler_", $this->attributes["name"], "(formObj) {";
			$elementSize = sizeof($this->elements);
			if(!empty($this->ajax))
				echo "\n\t\t", 'var form_data = ""';
			for($i = 0; $i < $elementSize; ++$i)
			{
				$ele = $this->elements[$i];
				$eleType = $ele->attributes["type"];
				$eleName = str_replace('"', '&quot;', $ele->attributes["name"]);
				$eleLabel = str_replace('"', '&quot;', strip_tags($ele->label));

				if($eleType == "checkbox")
				{
					echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].length) {';
						if(!empty($ele->required))
							echo "\n\t\t\tvar is_checked = false;";
						echo "\n\t\t\t", 'for(i = 0; i < formObj.elements["', $eleName, '"].length; i++) {';
							echo "\n\t\t\t\t", 'if(formObj.elements["', $eleName, '"][i].checked) {';
							if(!empty($this->ajax))
								echo "\n\t\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"][i].value);';
							if(!empty($ele->required))
								echo "\n\t\t\t\t\tis_checked = true;";
							echo "\n\t\t\t\t}";
						echo "\n\t\t\t}";		
						if(!empty($ele->required))
						{
							echo "\n\t\t\tif(!is_checked) {";
								echo "\n\t\t\t\t" , 'alert("', $eleLabel, ' is a required field.");';
								echo "\n\t\t\t\treturn false;";
							echo "\n\t\t\t}";
						}
					echo "\n\t\t}";		
					echo "\n\t\telse {";
					if(!empty($this->ajax))
					{
						echo "\n\t\t\t", 'if(formObj.elements["', $eleName, '"].checked)';
							echo "\n\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"].value);';
					}	
					if(!empty($ele->required))
					{
						echo "\n\t\t\t", 'if(!formObj.elements["', $eleName, '"].checked) {';
							echo "\n\t\t\t\t", 'alert("', $ele->label, ' is a required field.");';
							echo "\n\t\t\t\treturn false;";
						echo "\n\t\t\t}";
					}
					echo "\n\t\t}";
				}
				elseif($eleType == "text" || $eleType == "textarea" || $eleType == "select" || $eleType == "hidden" || $eleType == "file" || $eleType == "password" || $eleType == "captcha")
				{
					if(!empty($this->ajax))
						echo "\n\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"].value);';
					if(!empty($ele->required))
					{
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value == "") {';
							echo "\n\t\t\t" , 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\t" , 'formObj.elements["', $eleName, '"].focus();';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";
					}
				}
				elseif($eleType == "webeditor")
				{
					if(!empty($this->ajax))
						echo "\n\t\t", 'form_data += "&', $eleName, '=" + escape(tinyMCE.get("', $ele->attributes["id"], '").getContent());';
					if(!empty($ele->required))
					{
						echo "\n\t\t", 'if(tinyMCE.get("', $ele->attributes["id"], '").getContent() == "") {';
							echo "\n\t\t\t" , 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\t" , 'tinyMCE.get("', $ele->attributes["id"], '").focus();';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";
					}
				}
				elseif($eleType == "date")
				{
					if(!empty($this->ajax))
					{
						echo "\n\t\t", 'form_data += "&', $eleName, '="';
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value != "Click to Select Date...")';
							echo "\n\t\t\t", 'form_data += formObj.elements["', $eleName, '"].value;';
					}	
					if(!empty($ele->required))
					{
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value == "Click to Select Date...") {';
							echo "\n\t\t\t" , 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\t" , 'formObj.elements["', $eleName, '"].focus();';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";
					}

				}
				elseif($eleType == "daterange")
				{
					if(!empty($this->ajax))
					{
						echo "\n\t\t", 'form_data += "&', $eleName, '=";';
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value != "Click to Select Date Range...")';
							echo "\n\t\t\t", 'form_data += formObj.elements["', $eleName, '"].value;';
					}	
					if(!empty($ele->required))
					{
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value == "Click to Select Date Range...") {';
							echo "\n\t\t\t" , 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\t" , 'formObj.elements["', $eleName, '"].focus();';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";
					}

				}
				elseif($eleType == "latlng")
				{
					if(!empty($this->ajax))
					{
						echo "\n\t\t", 'form_data += "&', $eleName, '=";';
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value != "Drag Map Marker to Select Location...")';
							echo "\n\t\t\t", 'form_data += formObj.elements["', $eleName, '"].value;';
					}	
					if(!empty($ele->required))
					{
						echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].value == "Drag Map Marker to Select Location...") {';
							echo "\n\t\t\t", 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\t", 'formObj.elements["', $eleName, '"].focus();';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";
					}
				}
				elseif($eleType == "checksort")
				{
					if(!empty($this->ajax))
					{
						echo "\n\t\t", 'if(formObj.elements["', $eleName, '"]) {';
							echo "\n\t\t\t" , 'if(formObj.elements["', $eleName, '"].length) {';
								echo "\n\t\t\t\t" , 'var ulObj = document.getElementById("', str_replace('"', '&quot;', $ele->attributes["id"]), '");';
								echo "\n\t\t\t\tvar childLen = ulObj.childNodes.length;";
								echo "\n\t\t\t\tfor(i = 0; i < childLen; i++) {";
									echo "\n\t\t\t\t\t", 'childObj = document.getElementById("', str_replace('"', '&quot;', $ele->attributes["id"]), '").childNodes[i];';
										echo "\n\t\t\t\t\t", 'if(childObj.tagName && childObj.tagName.toLowerCase() == "li")';
											echo "\n\t\t\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(childObj.childNodes[0].value);';
								echo "\n\t\t\t\t}";
							echo "\n\t\t\t}";
							echo "\n\t\t\telse";
								echo "\n\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"].value);';
						echo "\n\t\t}";
					}
					if(!empty($ele->required))
					{
						echo "\n\t\t", 'if(!formObj.elements["', $eleName, '"]) {';
							echo "\n\t\t\t", 'alert("', $eleLabel, ' is a required field.");';
							echo "\n\t\t\treturn false;";
						echo "\n\t\t}";	
					}	
				}
				elseif(!empty($this->ajax) && $eleType == "radio")
				{
					echo "\n\t\t" , 'if(formObj.elements["', $eleName, '"].length) {';
						echo "\n\t\t\t", 'for(i = 0; i < formObj.elements["', $eleName, '"].length; i++) {';
							echo "\n\t\t\t\t", 'if(formObj.elements["', $eleName, '"][i].checked) {';
								echo "\n\t\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"][i].value);';
							echo "\n\t\t\t\t}";
						echo "\n\t\t\t}";		
					echo "\n\t\t}";		
					echo "\n\t\telse {";
						echo "\n\t\t\t", 'if(formObj.elements["', $eleName, '"].checked)';
							echo "\n\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"].value);';
					echo "\n\t\t}";
				}
				elseif(!empty($this->ajax) && $eleType == "sort")
				{
					echo "\n\t\t", 'if(formObj.elements["', $eleName, '"]) {';
						echo "\n\t\t\t" , 'if(formObj.elements["', $eleName, '"].length) {';
							echo "\n\t\t\t\t" , 'var ulObj = document.getElementById("', str_replace('"', '&quot;', $ele->attributes["id"]), '");';
							echo "\n\t\t\t\tvar childLen = ulObj.childNodes.length;";
							echo "\n\t\t\t\tfor(i = 0; i < childLen; i++) {";
								echo "\n\t\t\t\t\t", 'childObj = document.getElementById("', str_replace('"', '&quot;', $ele->attributes["id"]), '").childNodes[i];';
									echo "\n\t\t\t\t\t", 'if(childObj.tagName && childObj.tagName.toLowerCase() == "li")';
										echo "\n\t\t\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(childObj.childNodes[0].value);';
							echo "\n\t\t\t\t}";
						echo "\n\t\t\t}";
						echo "\n\t\t\telse";
							echo "\n\t\t\t\t", 'form_data += "&', $eleName, '=" + escape(formObj.elements["', $eleName, '"].value);';
					echo "\n\t\t}";
				}
			}	
			if(!empty($this->ajax))
			{
				echo "\n\t\tform_data = form_data.substring(1, form_data.length);";
				echo "\n\t\t$.ajax({";
					echo "\n\t\t\t", 'type: "', $this->ajaxType, '",';
					echo "\n\t\t\t", 'url: "', $this->ajaxUrl, '",';
					echo "\n\t\t\t", 'dataType: "', $this->ajaxDataType, '",';
					echo "\n\t\t\tdata: form_data,";
					echo "\n\t\t\tsuccess: function(responseMsg, textStatus) {";
					if(!empty($this->ajaxCallback))
						echo "\n\t\t\t\t", $this->ajaxCallback, "(responseMsg);";
					else
					{
						echo "\n\t\t\t\t", 'if(responseMsg != "")';
							echo "\n\t\t\t\t\talert(responseMsg);";
					}		
					echo "\n\t\t\t},";
					echo "\n\t\t\terror: function(XMLHttpRequest, textStatus, errorThrown) { alert(XMLHttpRequest.responseText); }";
				echo "\n\t\t});";
				echo "\n\t\treturn false;";
			}	
			else	
				echo "\n\t\treturn true;";
			echo "\n\t}";
			echo "\n</script>\n\n";
		}

		if(empty($this->noAutoFocus) && !empty($this->focusElement))
		{
			echo '<script language="javascript">';
			if(!empty($this->tinymceIDArr) && is_array($this->tinymceIDArr) && in_array($this->focusElement, $this->tinymceIDArr))
				echo "\n\t\t", 'setTimeout("if(tinyMCE.get(\"', $this->focusElement, '\")) tinyMCE.get(\"', $this->focusElement, '\").focus();", 500);';
			else
			{
				echo "\n\t", 'if(document.forms["', $this->attributes["name"], '"].elements["', $this->focusElement, '"].type != "select-one" && document.forms["', $this->attributes["name"], '"].elements["', $this->focusElement, '"].type != "select-multiple" && document.forms["', $this->attributes["name"], '"].elements["', $this->focusElement, '"].length)';
					echo "\n\t\t", 'document.forms["', $this->attributes["name"], '"].elements["', $this->focusElement, '"][0].focus();';
				echo "\n\telse";
					echo "\n\t\t", 'document.forms["', $this->attributes["name"], '"].elements["', $this->focusElement, '"].focus();';
			}		
			echo "\n</script>\n\n";
		}

		$content = ob_get_contents();
		ob_end_clean();
		echo($content);
	}

	/*This function builds and returns a string containing the HTML for the form fields.  Typeically, this will be called from within the render() function; however, it can also be called by the user during unique situations.*/
	public function elementsToString($includeTableTags = true)
	{
		$str = "";

		/*If this first non-hidden element is of type text, textarea, password, select, file, checkbox, or radio, then focus will automatically be applied.*/
		if(empty($this->noAutoFocus))
			$focus = true;
		else
			$focus = false;

		/*If this map array is set, an additional table will be inserted in each row - this way colspans can be omitted.*/
		if(!empty($this->map))
		{
			$mapIndex = 0;
			$mapCount = 0;
			if($includeTableTags)
				$str .= "\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
			if(!empty($this->tdAttributes["width"]))
				$mapOriginalWidth = $this->tdAttributes["width"];
		}	
		else
		{
			if($includeTableTags)
			{
				$str .= "\n<table";
				$tmpAllowFieldArr = $this->allowedFields["table"];
				if(!empty($this->tableAttributes) && is_array($this->tableAttributes))
				{
					foreach($this->tableAttributes as $key => $value)
					{
						if(in_array($key, $tmpAllowFieldArr))
							$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
					}		
				}
				$str .= ">\n";
			}
			
		}

		$elementSize = sizeof($this->elements);
		for($i = 0; $i < $elementSize; ++$i)
		{
			$ele = $this->elements[$i];

			/*If the referenceValues array is filled, check for this specific elemet's name in the associative array key and populate the field's value if applicable.*/
			if(!empty($this->referenceValues) && is_array($this->referenceValues) && array_key_exists($ele->attributes["name"], $this->referenceValues))
				$ele->attributes["value"] = $this->referenceValues[$ele->attributes["name"]];

			/*Hidden values do not need to be inside any table cell container; therefore, they are handled differently than the other fields.*/
			if($ele->attributes["type"] == "hidden")
			{
				if($includeTableTags)
				{
					$str .= "\t<input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["hidden"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					$str .= "/>\n";
				}
			}
			else
			{
				if(!empty($this->map))
				{
					if(array_key_exists($mapIndex, $this->map) && $this->map[$mapIndex] > 1)
					{
						if($mapCount == 0)
						{
							$str .= "\t" . '<tr><td style="padding: 0;">' . "\n";
							$str .= "\t\t<table";
							if(!empty($this->tableAttributes) && is_array($this->tableAttributes))
							{
								$tmpAllowFieldArr = $this->allowedFields["table"];
								foreach($this->tableAttributes as $key => $value)
								{
									if(in_array($key, $tmpAllowFieldArr))
										$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
								}		
							}
							$str .= ">\n";
							$str .= "\t\t\t<tr>\n\t\t\t\t";


							/*Widths are percentage based and are calculated by dividing 100 by the number of form fields in the given row.*/
							if(($elementSize - $i) < $this->map[$mapIndex])
								$this->tdAttributes["width"] = number_format(100 / ($elementSize - $i), 2, ".", "") . "%";
							else
								$this->tdAttributes["width"] = number_format(100 / $this->map[$mapIndex], 2, ".", "") . "%";
						}	
						else
							$str .= "\t\t\t\t";
					}
					else
					{
						$str .= "\t" . '<tr><td style="padding: 0;">' . "\n";
						$str .= "\t\t<table";
						if(!empty($this->tableAttributes) && is_array($this->tableAttributes))
						{
							$tmpAllowFieldArr = $this->allowedFields["table"];
							foreach($this->tableAttributes as $key => $value)
							{
								if(in_array($key, $tmpAllowFieldArr))
									$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
							}		
						}
						$str .= ">\n";
						$str .= "\t\t\t<tr>\n\t\t\t\t";
						if(!empty($mapOriginalWidth))
							$this->tdAttributes["width"] = $mapOriginalWidth;
						else
							unset($this->tdAttributes["width"]);
					}	
				}
				else
					$str .= "\t<tr>";

				$str .= "<td";
				if(!empty($this->tdAttributes) && is_array($this->tdAttributes))
				{
					$tmpAllowFieldArr = $this->allowedFields["td"];
					foreach($this->tdAttributes as $key => $value)
					{
						if(in_array($key, $tmpAllowFieldArr))
							$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
					}		
				}
				$str .= ">\n";

				if(!empty($ele->label))
				{
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";

					/*preHTML and postHTML allow for any special case scenarios.  One specific situation where these may be used would
					be if you need to toggle the visibility of an item or items based on the state of another field such as a radio button.*/
					if(!empty($ele->preHTML))
					{
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";
						$str .= $ele->preHTML;
						$str .= "\n";	
					}		

					/*Render the label inside a <div> tag.*/	
					$str .= "<div";
					if(!empty($this->labelAttributes) && is_array($this->labelAttributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["div"];
						foreach($this->labelAttributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					$str .= ">";

					/*If this field is set as required, render an "*" inside a <span> tag.*/
					if(!empty($ele->required))
					{
						$str .= " <span";
						if(!empty($this->requiredAttributes) && is_array($this->requiredAttributes))
						{
							$tmpAllowFieldArr = $this->allowedFields["span"];
							foreach($this->requiredAttributes as $key => $value)
							{
								if(in_array($key, $tmpAllowFieldArr))
									$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
							}		
						}
						$str .= ">*</span> ";
					}	
					$str .= $ele->label;

					/*jQuery Tooltip Functionality*/
					if(!empty($ele->tooltip))
					{
						if(empty($this->tooltipIcon))
							$this->tooltipIcon = $this->jqueryPath . "/qtip/tooltip_icon.png";

						if(!is_array($this->tooltipArr))
							$this->tooltipArr = array(); 

						/*jQuery tooltips need a unique id to function properly.*/
						$tooltipID = "tooltip_" . rand(0, 999);
						
						/*Ensure that this field id hasn't already been used.*/
						while(array_key_exists($tooltipID, $this->tooltipArr))
							$tooltipID = "tooltip_" . rand(0, 999);

						$this->tooltipArr[$tooltipID] = $ele->tooltip;	
						$str .= ' <img id="' . $tooltipID . '" src="' . $this->tooltipIcon . '">';
					}
					$str .= "</div>\n";
				}	

				/*Check the element's type and render the field accordinly.*/
				$eleType = &$ele->attributes["type"];
				if($eleType == "text" || $eleType == "password")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%;";
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["text"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					$str .= "/>\n";
					if($focus)
						$this->focusElement = $ele->attributes["name"];
				}
				elseif($eleType == "file")
				{
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["file"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					$str .= "/>\n";
					if($focus)
						$this->focusElement = $ele->attributes["name"];
				}
				elseif($eleType == "textarea")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%; height: 100px;";
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<textarea";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["textarea"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					$str .= ">" . $ele->attributes["value"] . "</textarea>\n";
					if($focus)
						$this->focusElement = $ele->attributes["name"];
				}
				elseif($eleType == "webeditor")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%; height: 100px;";
					
					if(empty($ele->attributes["class"]))
						$ele->attributes["class"] .= " ";

					if(!empty($ele->webeditorSimple))
						$ele->attributes["class"] .= "tiny_mce_simple";
					else
						$ele->attributes["class"] .= "tiny_mce";

					if(empty($ele->attributes["id"]))
						$ele->attributes["id"] = "webeditor_" . rand(0, 999);

					if(!is_array($this->tinymceIDArr))
						$this->tinymceIDArr = array();
					
					while(in_array($ele->attributes["id"], $this->tinymceIDArr))
						$ele->attributes["id"] = "webeditor_" . rand(0, 999);

					$this->tinymceIDArr[] = $ele->attributes["id"];	
						
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<textarea";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["textarea"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					$str .= ">" . $ele->attributes["value"] . "</textarea>\n";
					if($focus)
						$this->focusElement = $ele->attributes["id"];
				}
				elseif($eleType == "select")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%;";
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<select";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["select"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					if(!empty($ele->multiple))
						$str .= " multiple";
					$str .= "/>\n";

					$selected = false;
					if(is_array($ele->options))
					{
						$optionSize = sizeof($ele->options);
						for($o = 0; $o < $optionSize; ++$o)
						{
							$str .= "\t\t\t";
							if(!empty($this->map))
								$str .= "\t\t\t";
							$str .= '<option value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '"';
							if((!is_array($ele->attributes["value"]) && !$selected && $ele->attributes["value"] == $ele->options[$o]->value) || (is_array($ele->attributes["value"]) && in_array($ele->options[$o]->value, $ele->attributes["value"], true)))
							{
								$str .= " selected";
								$selected = true;
							}	
							$str .= '>' . $ele->options[$o]->text . "</option>\n"; 
						}	
					}

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "</select>\n";
					if($focus)
						$this->focusElement = $ele->attributes["name"];
				}
				elseif($eleType == "radio")
				{
					if(is_array($ele->options))
					{
						$optionSize = sizeof($ele->options);
						for($o = 0; $o < $optionSize; ++$o)
						{
							$str .= "\t\t";
							if(!empty($this->map))
								$str .= "\t\t\t";

							if($o != 0)
							{
								if(!empty($ele->nobreak))
									$str .= "&nbsp;&nbsp;";
								else
									$str .= "<br>";
							}	

							$str .= "<input";
							$tmpAllowFieldArr = $this->allowedFields["radio"];
							if(!empty($ele->attributes) && is_array($ele->attributes))
							{
								foreach($ele->attributes as $key => $value)
								{
									if(in_array($key, $tmpAllowFieldArr))
										$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
								}		
							}
							$str .= ' id="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '"';		
							if(($ele->attributes["value"] == "" && $o == 0) || $ele->attributes["value"] == $ele->options[$o]->value)
								$str .= " checked";
							if(!empty($ele->disabled))
								$str .= " disabled";
							$str .= '>';
							if(empty($this->noLabels))
								$str .= '<label for="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" style="cursor: pointer;">';
							$str .= $ele->options[$o]->text;
							if(empty($this->noLabels))
								 $str .= "</label>\n"; 
						}	
						if($focus)
							$this->focusElement = $ele->attributes["name"];
					}
				}
				elseif($eleType == "checkbox")
				{
					if(is_array($ele->options))
					{
						$optionSize = sizeof($ele->options);

						if($optionSize > 1 && substr($ele->attributes["name"], -2) != "[]")
							$ele->attributes["name"] .= "[]";

						for($o = 0; $o < $optionSize; ++$o)
						{
							$str .= "\t\t";
							if(!empty($this->map))
								$str .= "\t\t\t";

							if($o != 0)
							{
								if(!empty($ele->nobreak))
									$str .= "&nbsp;&nbsp;";
								else
									$str .= "<br>";
							}	

							$str .= "<input";
							if(!empty($ele->attributes) && is_array($ele->attributes))
							{
								$tmpAllowFieldArr = $this->allowedFields["checkbox"];
								foreach($ele->attributes as $key => $value)
								{
									if(in_array($key, $tmpAllowFieldArr))
										$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
								}		
							}
							$str .= ' id="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '"';		

							/*For checkboxes, the value parameter can be an array - which allows for multiple boxes to be checked by default.*/
							if((!is_array($ele->attributes["value"]) && $ele->attributes["value"] == $ele->options[$o]->value) || (is_array($ele->attributes["value"]) && in_array($ele->options[$o]->value, $ele->attributes["value"], true)))
								$str .= " checked";
							if(!empty($ele->disabled))
								$str .= " disabled";
							$str .= '>';
							if(empty($this->noLabels))
								$str .= '<label for="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" style="cursor: pointer;">';
							$str .= $ele->options[$o]->text;
							if(empty($this->noLabels))
								$str .= "</label>\n"; 
						}	
						if($focus)
							$this->focusElement = $ele->attributes["name"];
					}
				}
				elseif($eleType == "date")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%; cursor: pointer;";

					if(empty($ele->attributes["id"]))
						$ele->attributes["id"] = "dateinput_" . rand(0, 999);

					if(empty($ele->attributes["value"]))
						$ele->attributes["value"] = "Click to Select Date...";

					if(!is_array($this->jqueryDateArr))
						$this->jqueryDateArr = array();
					
					while(in_array($ele->attributes["id"], $this->jqueryDateArr))
						$ele->attributes["id"] = "dateinput_" . rand(0, 999);

					$this->jqueryDateArr[] = $ele->attributes["id"];	

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["date"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}	
					}
					$str .= " readonly";
					$str .= "/>\n";
				}
				elseif($eleType == "daterange")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%; cursor: pointer;";

					if(empty($ele->attributes["id"]))
						$ele->attributes["id"] = "daterangeinput_" . rand(0, 999);

					if(empty($ele->attributes["value"]))
						$ele->attributes["value"] = "Click to Select Date Range...";

					if(!is_array($this->jqueryDateRangeArr))
						$this->jqueryDateRangeArr = array();
					
					while(in_array($ele->attributes["id"], $this->jqueryDateRangeArr))
						$ele->attributes["id"] = "daterangeinput_" . rand(0, 999);

					$this->jqueryDateRangeArr[] = $ele->attributes["id"];	

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["date"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}	
					}
					$str .= " readonly";
					$str .= "/>\n";
				}
				elseif($eleType == "sort")
				{
					if(is_array($ele->options))
					{
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";

						if(empty($ele->attributes["id"]))
							$ele->attributes["id"] = "sort_" . rand(0, 999);
						if(substr($ele->attributes["name"], -2) != "[]")
							$ele->attributes["name"] .= "[]";

						$this->jquerySortArr[] = array($ele->attributes["id"], $ele->attributes["name"]);	

						$str .= '<ul id="' . str_replace('"', '&quot;', $ele->attributes["id"]) . '" style="list-style-type: none; margin: 2px 0 0 0; padding: 0; cursor: pointer;">' . "\n";
						$optionSize = sizeof($ele->options);
						for($o = 0; $o < $optionSize; ++$o)
						{
							$str .= "\t\t\t";
							if(!empty($this->map))
								$str .= "\t\t\t";
							$str .= '<li class="ui-state-default" style="margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 15px !important;"><input type="hidden" name="' . str_replace('"', '&quot;', $ele->attributes["name"]) . '" value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="position: absolute; margin-left: -1.3em;"></span>' . $ele->options[$o]->text . '</li>' . "\n";
						}	
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";
						$str .= "</ul>\n";
					}
				}
				elseif($eleType == "latlng")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%;";
					if(empty($ele->attributes["id"]))
						$ele->attributes["id"] = "latlnginput_" . rand(0, 999);
					
					$this->latlngArr[] = $ele;

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<input";
					/*Allowed fields used from date field type as they perform identically.*/
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["latlng"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}	
					}
					$str .= ' value="';
					if(empty($ele->attributes["value"]))
						$str .= "Drag Map Marker to Select Location...";
					elseif(!empty($ele->attributes["value"]) && is_array($ele->attributes["value"]))	
						$str .=  "Latitude: " . $ele->attributes["value"][0] . ", Longitude: " . $ele->attributes["value"][1];
					$str .= '"';

					$str .= " readonly";
					$str .= "/>\n";

					if(empty($ele->latlngHeight))
						$ele->latlngHeight = 200;

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= '<div id="' . str_replace('"', '&quot;', $ele->attributes["id"]) . '_canvas" style="margin-top: 2px; height: ' . $ele->latlngHeight . 'px;';
					if(!empty($ele->latlngWidth))
						$str .= ' width: ' . $ele->latlngWidth . 'px;';
					$str .= '"></div>' . "\n";
				}
				elseif($eleType == "checksort")
				{
					if(is_array($ele->options))
					{
						if(empty($ele->attributes["id"]))
							$ele->attributes["id"] = "checksort_" . rand(0, 999);
						if(substr($ele->attributes["name"], -2) != "[]")
							$ele->attributes["name"] .= "[]";

						$this->jquerySortArr[] = array($ele->attributes["id"], $ele->attributes["name"]);	
						$this->jqueryCheckSort = 1;

						/*Temporary variable for building <ul> sorting structure for checked options.*/
						$sortLIArr = array();

						$optionSize = sizeof($ele->options);
						for($o = 0; $o < $optionSize; ++$o)
						{
							$str .= "\t\t";
							if(!empty($this->map))
								$str .= "\t\t\t";

							if($o != 0)
							{
								if(!empty($ele->nobreak))
									$str .= "&nbsp;&nbsp;";
								else
									$str .= "<br>";
							}	

							$str .= "<input";
							if(!empty($ele->attributes) && is_array($ele->attributes))
							{
								$tmpAllowFieldArr = $this->allowedFields["checksort"];
								foreach($ele->attributes as $key => $value)
								{
									if(in_array($key, $tmpAllowFieldArr))
										$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
								}		
							}
							$str .= ' id="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" type="checkbox" value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '" onclick="addOrRemoveCheckSortItem_' . $this->attributes["name"] . '(this, \'' . str_replace(array('"', "'"), array('&quot;', "\'"), $ele->attributes["id"]) . '\', \'' . str_replace(array('"', "'"), array('&quot;', "\'"), $ele->attributes["name"]) . '\', ' . $o . ', \'' . str_replace(array('"', "'"), array('&quot;', "\'"), $ele->options[$o]->value) . '\', \'' . str_replace(array('"', "'"), array('&quot;', "\'"), $ele->options[$o]->text) . '\');"';
							/*For checkboxes, the value parameter can be an array - which allows for multiple boxes to be checked by default.*/
							if((!is_array($ele->attributes["value"]) && $ele->attributes["value"] == $ele->options[$o]->value) || (is_array($ele->attributes["value"]) && in_array($ele->options[$o]->value, $ele->attributes["value"], true)))
							{
								$str .= " checked";
								$sortLIArr[$ele->options[$o]->value] = '<li class="ui-state-default" id="' . str_replace('"', '&quot;', $ele->attributes["id"]) . $o . '" style="margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 15px !important;"><input type="hidden" name="' . str_replace('"', '&quot;', $ele->attributes["name"]) . '" value="' . str_replace('"', '&quot;', $ele->options[$o]->value) . '"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="position: absolute; margin-left: -1.3em;"></span>' . $ele->options[$o]->text . '</li>' . "\n";
							}	
							if(!empty($ele->disabled))
								$str .= " disabled";
							$str .= '>';
							if(empty($this->noLabels))
								$str .= '<label for="' . str_replace('"', '&quot;', $ele->attributes["name"]) . $o . '" style="cursor: pointer;">';
							$str .= $ele->options[$o]->text;
							if(empty($this->noLabels))
								 $str .= "</label>\n"; 
						}	

						/*If there are any check options by default, render the <ul> sorting structure.*/
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";
						$str .= '<ul id="' . str_replace('"', '&quot;', $ele->attributes["id"]) . '" style="list-style-type: none; margin: 2px 0 0 0; padding: 0; cursor: pointer;">' . "\n";
						if(!empty($sortLIArr))
						{
							if(is_array($ele->attributes["value"]))
							{
								$eleValueSize = sizeof($ele->attributes["value"]);
								for($li = 0; $li < $eleValueSize; $li++)
								{
									if(isset($sortLIArr[$ele->attributes["value"][$li]]))
									{
										$str .= "\t\t\t";
										if(!empty($this->map))
											$str .= "\t\t\t\t";
										$str .= $sortLIArr[$ele->attributes["value"][$li]];	
									}
								}
							}
							else
							{
								if(isset($sortLIArr[$ele->attributes["value"][$li]]))
								{
									$str .= "\t\t\t";
									if(!empty($this->map))
										$str .= "\t\t\t\t";
									$str .= $sortLIArr[$ele->attributes["value"]];
								}
							}		
						}
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";
						$str .= "</ul>\n";
					}
				}
				elseif($eleType == "captcha")
				{
					if(empty($ele->attributes["style"]))
						$ele->attributes["style"] = "width: 100%;";

					if(!is_array($this->captchaNameArr))
						$this->captchaNameArr = array();
					
					if(empty($ele->attributes["name"]))
						$ele->attributes["name"] = "captchainput_0";

					$captchaCounter = 1;
					while(in_array($ele->attributes["name"], $this->captchaNameArr))
					{
						$ele->attributes["name"] = "captchainput_" . $captchaCounter;
						$captchaCounter++;
					}	

					$this->captchaNameArr[] = $ele->attributes["name"];	

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= '<img src="' . $this->captchaPath . '/captcha.php?fid=' . $this->attributes["name"] . '&eid=' . $ele->attributes["name"] . '" border="0" style="margin: 5px 0;">' . "\n";
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= "<br><input";
					if(!empty($ele->attributes) && is_array($ele->attributes))
					{
						$tmpAllowFieldArr = $this->allowedFields["text"];
						foreach($ele->attributes as $key => $value)
						{
							if(in_array($key, $tmpAllowFieldArr))
								$str .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
						}		
					}
					if(!empty($ele->disabled))
						$str .= " disabled";
					if(!empty($ele->readonly))
						$str .= " readonly";
					$str .= "/>\n";
					if($focus)
						$this->focusElement = $ele->attributes["name"];
				}
				elseif($eleType == "slider")
				{
					if(empty($ele->attributes["id"]))
						$ele->attributes["id"] = "sliderinput_" . rand(0, 999);

					if(!is_array($this->jquerySliderArr))
						$this->jquerySliderArr = array();
					while(in_array($ele->attributes["id"], $this->jquerySliderArr))
						$ele->attributes["id"] = "sliderinput_" . rand(0, 999);

					if(empty($ele->attributes["value"]))
						$ele->attributes["value"] = "0";

					if(empty($ele->sliderMin))
						$ele->sliderMin = "0";

					if(empty($ele->sliderMax))
						$ele->sliderMax = "100";

					if(empty($ele->sliderOrientation) || !in_array($ele->sliderOrientation, array("horizontal", "vertical")))
						$ele->sliderOrientation = "horizontal";

					if(empty($ele->sliderPrefix))
						$ele->sliderPrefix = "";

					if(empty($ele->sliderSuffix))
						$ele->sliderSuffix = "";
					
					if(is_array($ele->attributes["value"]) && sizeof($ele->attributes["value"]) == 1)
						$ele->attributes["value"] = $ele->attributes["value"][0];
					
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= '<div id="' . $ele->attributes["id"] . '" style="font-size: 12px !important; margin: 2px 0;';
					if($ele->sliderOrientation == "vertical" && !empty($ele->sliderHeight))
					{
						if(substr($ele->sliderHeight, -2) != "px")
							$ele->sliderHeight .= "px";
						$str .= ' height: ' . $ele->sliderHeight;
					}	
					$str .= '"></div>' . "\n";

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					
					if(empty($ele->sliderHideDisplay))
					{
						$str .= '<div id="' . $ele->attributes["id"] . '_display">';
						if(is_array($ele->attributes["value"]))
						{
							sort($ele->attributes["value"]);
							$str .= $ele->sliderPrefix . $ele->attributes["value"][0] . $ele->sliderSuffix . " - " . $ele->sliderPrefix . $ele->attributes["value"][1] . $ele->sliderSuffix;
						}	
						else
							$str .= $ele->sliderPrefix . $ele->attributes["value"] . $ele->sliderSuffix;
						$str .= '</div>' . "\n";	
					}

					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					if(is_array($ele->attributes["value"]))
					{
						if(substr($ele->attributes["name"], -2) != "[]")
							$ele->attributes["name"] .= "[]";
						$str .= '<input type="hidden" name="' . str_replace('"', '&quot;', $ele->attributes["name"]) . '" value="' . str_replace('"', '&quot;', $ele->attributes["value"][0]) . '">' . "\n";
						$str .= "\t\t";
						if(!empty($this->map))
							$str .= "\t\t\t";
						$str .= '<input type="hidden" name="' . str_replace('"', '&quot;', $ele->attributes["name"]) . '" value="' . str_replace('"', '&quot;', $ele->attributes["value"][1]) . '">' . "\n";
					}
					else
						$str .= '<input type="hidden" name="' . str_replace('"', '&quot;', $ele->attributes["name"]) . '" value="' . str_replace('"', '&quot;', $ele->attributes["value"]) . '">' . "\n";

					$this->jquerySliderArr[] = $ele;
				}

				if(!empty($ele->postHTML))
				{
					$str .= "\t\t";
					if(!empty($this->map))
						$str .= "\t\t\t";
					$str .= $ele->postHTML;
					$str .= "\n";	
				}		

				$str .= "\t";
				if(!empty($this->map))
					$str .= "\t\t\t";
				$str .= "</td>";

				if(!empty($this->map))
				{
					if(($i + 1) == $elementSize)
						$str .= "\n\t\t\t</tr>\n\t\t</table>\n\t</td></tr>\n";
					elseif(array_key_exists($mapIndex, $this->map) && $this->map[$mapIndex] > 1)
					{
						if(($mapCount + 1) == $this->map[$mapIndex])
						{
							$mapCount = 0;
							++$mapIndex;
							$str .= "\n\t\t\t</tr>\n\t\t</table>\n\t</td></tr>\n";
						}
						else
						{
							++$mapCount;
							$str .= "\n";
						}	
					}
					else
					{
						++$mapIndex;
						$mapCount = 0;
						$str .= "\n\t\t\t</tr>\n\t\t</table>\n\t</td></tr>\n";
					}	
				}
				else
					$str .= "</tr>\n";
				$focus = false;
			}	
		}

		if(!empty($this->map) && !empty($mapOriginalWidth))
			$this->tdAttributes["width"] = $mapOriginalWidth;
		else
			unset($this->tdAttributes["width"]);

		if($includeTableTags)
			$str .= "</table>\n";

		/*The two jQuery hybrid fields - sort and date - require javascript and css elements to be included in the markup.  Feel free
		to edit the css and/or javascript to fit your needs.  For instance, you can change the date field's formatted date string below, or
		change how the sort operates.  Visit http://www.jqueryui.com for instructions/examples on how to do this.*/
		if(!empty($this->jqueryDateArr) || !empty($this->jqueryDateRangeArr) || !empty($this->jquerySortArr) || !empty($this->tooltipArr) || !empty($this->jquerySliderArr))
		{
			if(empty($this->preventJQueryLoad))
				$str .= "\n\t" . '<script language="javascript" src="' . $this->jqueryPath . '/jquery-1.3.2.min.js"></script>';
			if(!empty($this->jqueryDateArr) || !empty($this->jqueryDateRangeArr) || !empty($this->jquerySortArr) || !empty($this->jquerySliderArr))
			{
				$str .= "\n\t" . '<link href="' . $this->jqueryPath . '/jquery-ui-1.7.2.custom.css" rel="stylesheet" type="text/css">';
				if(empty($this->preventJQueryUILoad))
					$str .= "\n\t" . '<script language="javascript" src="' . $this->jqueryPath . '/jquery-ui-1.7.2.custom.min.js"></script>';
			}
			if(!empty($this->tooltipArr) && empty($this->preventQTipLoad))
				$str .= "\n\t" . '<script language="javascript" src="' . $this->jqueryPath . '/qtip/jquery.qtip-1.0.0-rc3.min.js"></script>';

			if(!empty($this->jqueryDateArr))
				$str .= "\n\t" . '<style type="text/css">.ui-datepicker-div, .ui-datepicker-inline, #ui-datepicker-div { font-size: 0.8em !important; }</style>';

			if(!empty($this->jquerySliderArr))
				$str .= "\n\t" . '<style type="text/css">.ui-slider-handle { cursor: pointer !important; }</style>';

			if(!empty($this->jqueryDateRangeArr))
			{
				$str .= "\n\t" . '<link href="' . $this->jqueryPath . '/ui.daterangepicker.css" rel="stylesheet" type="text/css">';
				$str .= "\n\t" . '<script language="javascript" src="' . $this->jqueryPath . '/daterangepicker.jquery.js"></script>';
			}	

			$str .= "\n\t" . '<script language="javascript" defer="true">';
			$str .= "\n\t\t" . "$(function() {";
			if(!empty($this->jqueryDateArr))
			{
				$jquerySize = sizeof($this->jqueryDateArr);
				for($j = 0; $j < $jquerySize; ++$j)
					$str .= "\n\t\t\t" . '$("#' . $this->jqueryDateArr[$j] . '").datepicker({ dateFormat: "MM d, yy", showButtonPanel: true });';
			}

			if(!empty($this->jqueryDateRangeArr))
			{
				$jquerySize = sizeof($this->jqueryDateRangeArr);
				for($j = 0; $j < $jquerySize; ++$j)
					$str .= "\n\t\t\t" . '$("#' . $this->jqueryDateRangeArr[$j] . '").daterangepicker();';
			}

			if(!empty($this->jquerySortArr))
			{
				$jquerySize = sizeof($this->jquerySortArr);
				for($j = 0; $j < $jquerySize; ++$j)
				{
					$str .= "\n\t\t\t" . '$("#' . $this->jquerySortArr[$j][0] . '").sortable({ axis: "y" });';
					$str .= "\n\t\t\t" . '$("#' . $this->jquerySortArr[$j][0] . '").disableSelection();';
				}	
			}

			/*For more information on qtip, visit http://craigsworks.com/projects/qtip/.*/
			if(!empty($this->tooltipArr))
			{
				$tooltipKeys = array();
				$tooltipKeys = array_keys($this->tooltipArr);
				$tooltipSize = sizeof($tooltipKeys);

				for($j = 0; $j < $tooltipSize; ++$j)
				{
					$str .= "\n\t\t\t" . '$("#' . $tooltipKeys[$j] . '").qtip({ content: "' . str_replace('"', '\"', $this->tooltipArr[$tooltipKeys[$j]]) . '", style: { name: "light", tip: "bottomLeft", border: { radius: 5, width: 5';
					if(!empty($this->tooltipBorderColor))
					{
						if($this->tooltipBorderColor[0] != "#")
							$this->tooltipBorderColor = "#" . $this->tooltipBorderColor;
						$str .= ', color: "' . $this->tooltipBorderColor . '"';
					}	
					$str .= ' } }, position: { corner: { target: "topRight", tooltip: "bottomLeft" } } });';
				}	
			}

			if(!empty($this->jquerySliderArr))
			{
				$jquerySize = sizeof($this->jquerySliderArr);
				for($j = 0; $j < $jquerySize; ++$j)
				{
					$slider = $this->jquerySliderArr[$j];
					$str .= "\n\t\t\t" . '$("#' . $slider->attributes["id"] . '").slider({';
					if(is_array($slider->attributes["value"]))
						$str .= 'range: true, values: [' . $slider->attributes["value"][0] . ', ' . $slider->attributes["value"][1] . ']';
					else
						$str .= 'range: "min", value: ' . $slider->attributes["value"];
					$str .= ', min: ' . $slider->sliderMin . ', max: ' . $slider->sliderMax . ', orientation: "' . $slider->sliderOrientation . '"';
					if(!empty($slider->sliderSnapIncrement))
						$str .= ', step: ' . $slider->sliderSnapIncrement;
					if(is_array($slider->attributes["value"]))
					{
						$str .= ', slide: function(event, ui) { ';
						if(empty($slider->sliderHideDisplay))
							$str .= '$("#' . $slider->attributes["id"] . '_display").text("' . $slider->sliderPrefix . '" + ui.values[0] + "' . $slider->sliderSuffix . ' - ' . $slider->sliderPrefix . '" + ui.values[1] + "' . $slider->sliderSuffix . '"); ';
						$str .= 'document.forms["' . $this->attributes["name"] . '"].elements["' . str_replace('"', '&quot;', $slider->attributes["name"]) . '"][0].value = ui.values[0]; document.forms["' . $this->attributes["name"] . '"].elements["' . str_replace('"', '&quot;', $slider->attributes["name"]) . '"][1].value = ui.values[1];}';
					}	
					else
					{
						$str .= ', slide: function(event, ui) { ';
						if(empty($slider->sliderHideDisplay))
							$str .= '$("#' . $slider->attributes["id"] . '_display").text("' . $slider->sliderPrefix . '" + ui.value + "' . $slider->sliderSuffix . '");';
						$str .= ' document.forms["' . $this->attributes["name"] . '"].elements["' . str_replace('"', '&quot;', $slider->attributes["name"]) . '"].value = ui.value;}';
					}	
					$str .= '});';
				}
			}
			$str .= "\n\t\t});";

			$str .= "\n\t</script>\n\n";
		}	
		elseif(!empty($this->ajax) && empty($this->preventJQueryLoad))
			$str .= "\n\t" . '<script language="javascript" src="' . $this->jqueryPath . '/jquery-1.3.2.min.js"></script>';

		if(!empty($this->latlngArr) && !empty($this->googleMapsAPIKey))
		{
			if(empty($this->preventGoogleMapsLoad))
				$str .= "\n\t" . '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $this->googleMapsAPIKey . '" type="text/javascript"></script>';
			$str .= "\n\t" . '<script language="javascript" defer="true">';
			$str .= "\n\t\tfunction initializeLatLng_" . $this->attributes["name"] . "() {";
				$str .= "\n\t\t\tif (GBrowserIsCompatible()) {";
				$latlngSize = sizeof($this->latlngArr);
				for($l = 0; $l < $latlngSize; ++$l)
				{
					$latlng = $this->latlngArr[$l];
					$latlngID = str_replace('"', '&quot;', $latlng->attributes["id"]);
					if(!empty($latlng->attributes["value"]))
					{
						$latlngCenter = $latlng->attributes["value"];
						if(empty($latlng->latlngZoom))
							$latlngZoom = 9;
						else
							$latlngZoom = $latlng->latlngZoom;
					}		
					else	
					{
						$latlngCenter = array(39, -96);
						if(empty($latlng->latlngZoom))
							$latlngZoom = 3;
						else
							$latlngZoom = $latlng->latlngZoom;
					}	
					$str .= "\n\t\t\t\t" . 'var map_' . $latlngID . ' = new GMap2(document.getElementById("' . $latlngID . '_canvas"));';
					$str .= "\n\t\t\t\t" . 'map_' . $latlngID . '.addControl(new GSmallMapControl());';
					$str .= "\n\t\t\t\t" . 'var center_' . $latlngID . ' = new GLatLng(' . $latlngCenter[0] . ', ' . $latlngCenter[1] . ');';
					$str .= "\n\t\t\t\t" . 'map_' . $latlngID . '.setCenter(center_' . $latlngID . ', ' . $latlngZoom . ');';
					$str .= "\n\t\t\t\t" . 'var marker_' . $latlngID . ' = new GMarker(center_' . $latlngID . ', {draggable: true});';
					$str .= "\n\t\t\t\t" . 'map_' . $latlngID . '.addOverlay(marker_' . $latlngID . ');';
					$str .= "\n\t\t\t\t" . 'GEvent.addListener(marker_' . $latlngID . ', "dragend", function() {';
					$str .= "\n\t\t\t\t\tvar latlng = marker_" . $latlngID . ".getLatLng();";
					$str .= "\n\t\t\t\t\tvar lat = latlng.lat();";
					$str .= "\n\t\t\t\t\tvar lng = latlng.lng();";
					$str .= "\n\t\t\t\t\t" . 'document.forms["' . $this->attributes["name"] . '"].elements["' . str_replace('"', '&quot;', $latlng->attributes["name"]) . '"].value = "Latitude: " + lat.toFixed(3) + ", Longitude: " + lng.toFixed(3);';
					$str .= "\n\t\t\t\t});";
				}
				$str .= "\n\t\t\t}";
			$str .= "\n\t\t}";
			$str .= "\n\t\t" . 'if(window.addEventListener) { window.addEventListener("load", initializeLatLng_' . $this->attributes["name"] . ', false); }'; 
			$str .= "\n\t\t" . 'else if(window.attachEvent) { window.attachEvent("onload", initializeLatLng_' . $this->attributes["name"] . '); }'; 
			$str .= "\n\t</script>\n\n";
		}

		if(!empty($this->jqueryCheckSort))
		{
			$str .= "\n\t" . '<script language="javascript" defer="true">';
				$str .= "\n\t\tfunction addOrRemoveCheckSortItem_" . $this->attributes["name"] . "(cs_fieldObj, cs_id, cs_name, cs_index, cs_value, cs_text) {";
					$str .= "\n\t\t\tif(cs_fieldObj.checked != true)";
						$str .= "\n\t\t\t\t" . 'document.getElementById(cs_id).removeChild(document.getElementById(cs_id + cs_index));';
					$str .= "\n\t\t\telse {";
						$str .= "\n\t\t\t\tvar li = document.createElement('li');";
						$str .= "\n\t\t\t\tli.id = cs_id + cs_index;";
						$str .= "\n\t\t\t\tli.className = 'ui-state-default';";
						$str .= "\n\t\t\t\tli.style.cssText = 'margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 15px !important;'";
						$str .= "\n\t\t\t\tli.innerHTML = '<input type=\"hidden\" name=\"' + cs_name + '\" value=\"' + cs_value + '\"><span class=\"ui-icon ui-icon-arrowthick-2-n-s\" style=\"position: absolute; margin-left: -1.3em;\"></span>' + cs_text;";
						$str .= "\n\t\t\t\tdocument.getElementById(cs_id).appendChild(li);";
					$str .= "\n\t\t\t}";
				$str .= "\n\t\t}";
			$str .= "\n\t</script>\n\n";
		}

		if(!empty($this->includeTinyMce))
		{
			$str .= "\n\t" . '<script language="javascript" src="' . $this->tinymcePath . '/tiny_mce.js"></script>';
			$str .= "\n\t" . '<script language="javascript">';
				$str .= "\n\t\ttinyMCE.init({";
					$str .= "\n\t\t\t" . 'mode: "textareas",';
					$str .= "\n\t\t\t" . 'theme: "advanced",';
					$str .= "\n\t\t\t" . 'plugins: "safari,table,paste",';
					$str .= "\n\t\t\t" . 'theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,forecolor,backcolor",';
					$str .= "\n\t\t\t" . 'theme_advanced_buttons2: "formatselect,fontselect,fontsizeselect,|,pastetext,pasteword,|,link,image",';
					$str .= "\n\t\t\t" . 'theme_advanced_buttons3: "tablecontrols,|,code,cleanup,|,undo,redo",';
					$str .= "\n\t\t\t" . 'theme_advanced_toolbar_location: "top",';
					$str .= "\n\t\t\t" . 'editor_selector: "tiny_mce"';
				$str .= "\n\t\t});";
				$str .= "\n\t\ttinyMCE.init({";
					$str .= "\n\t\t\t" . 'mode: "textareas",';
					$str .= "\n\t\t\t" . 'theme: "simple",';
					$str .= "\n\t\t\t" . 'editor_selector: "tiny_mce_simple"';
				$str .= "\n\t\t});";
			$str .= "\n\t</script>\n\n";
		}

		return $str;
	}

	/*This function handles php form checking to be used after submission.  Serialization needs to be utilized before calling this function to revive the forms past state.  If returnUrl is set, this function will redirect and exit.*/
	public function checkForm()
	{
		if((empty($this->ajax) && strtolower($this->attributes["method"]) == "post") || (!empty($this->ajax) && strtolower($this->ajaxType) == "post"))
			$ref = $_POST;
		else
			$ref = $_GET;

		$error_msg = "";
		$elementSize = sizeof($this->elements);
		for($i = 0; $i < $elementSize; ++$i)
		{
			$ele = $this->elements[$i];
			if(!empty($ele->required))
			{
				/*Radio buttons and the sort element types are ignored.*/
				if($ele->attributes["type"] == "radio" || $ele->attributes["type"] == "sort")
					continue;
				elseif($ele->attributes["type"] == "date" && (empty($ref[$ele->attributes["name"]]) || $ref[$ele->attributes["name"]] == "Click to Select Date..."))
				{
					$error_msg = $ele->label . " is a required field.";	
					break;
				}
				elseif($ele->attributes["type"] == "daterange" && (empty($ref[$ele->attributes["name"]]) || $ref[$ele->attributes["name"]] == "Click to Select Date Range..."))
				{
					$error_msg = $ele->label . " is a required field.";	
					break;
				}
				elseif($ele->attributes["type"] == "latlng" && (empty($ref[$ele->attributes["name"]]) || $ref[$ele->attributes["name"]] == "Drag Map Marker to Select Location..."))
				{
					$error_msg = $ele->label . " is a required field.";	
					break;
				}
				elseif(($ele->attributes["type"] == "checkbox" || $ele->attributes["type"] == "checksort") && !isset($ref[$ele->attributes["name"]]))
				{
					$error_msg = $ele->label . " is a required field.";	
					break;
				}
				elseif($ref[$ele->attributes["name"]] == "")
				{
					$error_msg = $ele->label . " is a required field.";	
					break;
				}	
			}
		}	

		if(!empty($error_msg))
		{
			if(!empty($this->ajax))
			{
				echo($error_msg);
				exit();
			}
			else
			{
				if(strpos($this->returnUrl, "?") === false)
					$error_msg = "?error_message=" . rawurlencode($error_msg);
				else	
					$error_msg = "&error_message=" . rawurlencode($error_msg);

				if(!empty($this->returnUrl))
				{
					header("Location: " . $this->returnUrl . $error_msg);
					exit();
				}
				else
					return $error_msg;
			}	
		}
		else 
			return;
	}

	/*This function set the referenceValues variables which can be used to pre-fill form fields.  This function needs to be called before the render function.*/
	public function setReferenceValues($ref)
	{
		$this->referenceValues = $ref;
	}
}

class element extends HelperBase {
	/*Public variables to be read/written in both the base and form classes.*/
	public $attributes;
	public $label;
	public $options;
	public $required;
	public $disabled;
	public $multiple;
	public $readonly;
	public $nobreak;
	public $preHTML;
	public $postHTML;
	public $tooltip;

	/*webeditor specific fields*/
	public $webeditorSimple;

	/*latlng specific fields*/
	public $latlngHeight;
	public $latlngWidth;
	public $latlngZoom;

	/*slider specific fields*/
	public $sliderMin;
	public $sliderMax;
	public $sliderSnapIncrement;
	public $sliderOrientation;
	public $sliderPrefix;
	public $sliderSuffix;
	public $sliderHeight;
	public $sliderHideDisplay;

	public function __construct() {
		/*Set default values where appropriate.*/
		$this->attributes = array(
			"type" => "text"
		);
	}
}
class option extends HelperBase {
	/*Public variables to be read/written in both the base and form classes.*/
	public $value;
	public $text;
}
class button extends HelperBase {
	/*Public variables to be read/written in both the base and form classes.*/
	public $attributes;
	public $phpFunction;
	public $phpParams;
	public $wrapLink;
	public $linkAttributes;

	/*Set default values where appropriate.*/
	public function __construct() {
		$this->linkAttributes = array(
			"style" => "text-decoration: none;"
		);
	}
}
?>
