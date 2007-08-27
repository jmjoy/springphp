<?php
/*
 * Copyright 2007 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
function get_month_names($month_type = '') {
	if ($month_type == 'short') {
		$month_names = array (
			1 => 'jan',
			2 => 'feb',
			3 => 'mar',
			4 => 'apr',
			5 => 'may',
			6 => 'jun',
			7 => 'jul',
			8 => 'aug',
			9 => 'sep',
			10 => 'oct',
			11 => 'nov',
			12 => 'dec'
		);
	} else {
		$month_names = array (
			1 => 'january',
			2 => 'february',
			3 => 'march',
			4 => 'april',
			5 => 'may',
			6 => 'june',
			7 => 'july',
			8 => 'august',
			9 => 'september',
			10 => 'october',
			11 => 'november',
			12 => 'december'
		);
	}

	$months = array ();
	foreach ($month_names as $num => $val) {
		$months[$num] = __(ucfirst($val));
	}

	return $months;
}

function get_day_names($day_type = '') {
	if ($day_type == 'long') {
		$day_names = array (
			0 => 'sunday',
			1 => 'monday',
			2 => 'tuesday',
			3 => 'wednesday',
			4 => 'thursday',
			5 => 'friday',
			6 => 'saturday'
		);
	}
	elseif ($day_type == 'short') {
		$day_names = array (
			0 => 'sun',
			1 => 'mon',
			2 => 'tue',
			3 => 'wed',
			4 => 'thu',
			5 => 'fri',
			6 => 'sat'
		);
	} else {
		$day_names = array (
			0 => 'su',
			1 => 'mo',
			2 => 'tu',
			3 => 'we',
			4 => 'th',
			5 => 'fr',
			6 => 'sa'
		);
	}

	$days = array ();
	foreach ($day_names as $num => $val) {
		$days[$num] = __(ucfirst($val));
	}

	return $days;
}

function get_timezones() {

	$timezone_names = array(
		'UM12' => "(UTC - 12:00) Enitwetok, Kwajalien",
		'UM11' => "(UTC - 11:00) Nome, Midway Island, Samoa",
		'UM10' => "(UTC - 10:00) Hawaii",
		'UM9' => "(UTC - 9:00) Alaska",
		'UM8' => "(UTC - 8:00) Pacific Time",
		'UM7' => "(UTC - 7:00) Mountain Time",
		'UM6' => "(UTC - 6:00) Central Time, Mexico City",
		'UM5' => "(UTC - 5:00) Eastern Time, Bogota, Lima, Quito",
		'UM4' => "(UTC - 4:00) Atlantic Time, Caracas, La Paz",
		'UM25' => "(UTC - 3:30) Newfoundland",
		'UM3' => "(UTC - 3:00) Brazil, Buenos Aires, Georgetown, Falkland Is.",
		'UM2' => "(UTC - 2:00) Mid-Atlantic, Ascention Is., St Helena",
		'UM1' => "(UTC - 1:00) Azores, Cape Verde Islands",
		'UTC' => "(UTC) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia",
		'UP1' => "(UTC + 1:00) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome",
		'UP2' => "(UTC + 2:00) Kaliningrad, South Africa, Warsaw",
		'UP3' => "(UTC + 3:00) Baghdad, Riyadh, Moscow, Nairobi",
		'UP25' => "(UTC + 3:30) Tehran",
		'UP4' => "(UTC + 4:00) Adu Dhabi, Baku, Muscat, Tbilisi",
		'UP35' => "(UTC + 4:30) Kabul",
		'UP5' => "(UTC + 5:00) Islamabad, Karachi, Tashkent",
		'UP45' => "(UTC + 5:30) Bombay, Calcutta, Madras, New Delhi",
		'UP6' => "(UTC + 6:00) Almaty, Colomba, Dhakra",
		'UP7' => "(UTC + 7:00) Bangkok, Hanoi, Jakarta",
		'UP8' => "(UTC + 8:00) Beijing, Hong Kong, Perth, Singapore, Taipei",
		'UP9' => "(UTC + 9:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk",
		'UP85' => "(UTC + 9:30) Adelaide, Darwin",
		'UP10' => "(UTC + 10:00) Melbourne, Papua New Guinea, Sydney, Vladivostok",
		'UP11' => "(UTC + 11:00) Magadan, New Caledonia, Solomon Islands",
		'UP12' => "(UTC + 12:00) Auckland, Wellington, Fiji, Marshall Island"	
	);
	
	$timezones = array();
	foreach($timezone_names as $name => $value) 
		$timezones[$name] = __($value);

	return $timezones;
}

function get_timezone_offsets($timezone = '') {


	$zones = array(
					'UM12' => -12,
					'UM11' => -11,
					'UM10' => -10,
					'UM9'  => -9,
					'UM8'  => -8,
					'UM7'  => -7,
					'UM6'  => -6,
					'UM5'  => -5,
					'UM4'  => -4,
					'UM25' => -2.5,
					'UM3'  => -3,
					'UM2'  => -2,
					'UM1'  => -1,
					'UTC'  => 0,
					'UP1'  => +1,
					'UP2'  => +2,
					'UP3'  => +3,
					'UP25' => +2.5,
					'UP4'  => +4,
					'UP35' => +3.5,
					'UP5'  => +5,
					'UP45' => +4.5,
					'UP6'  => +6,
					'UP7'  => +7,
					'UP8'  => +8,
					'UP9'  => +9,
					'UP85' => +8.5,
					'UP10' => +10,
					'UP11' => +11,
					'UP12' => +12
				);
				
	if ($timezone == '')
	{
		return $zones;
	}
	
	if ($timezone == 'GMT')
		$timezone = 'UTC';
	
	return ( ! isset($zones[$timezone])) ? 0 : $zones[$timezone];
}

function get_countries() {

	$country_names = array(	
		'US' => 'United States',
		'CA' => 'Canada',
		'DK' => 'Denmark',
		'DE' => 'Germany',
		'GB' => 'Great Britain',
		'IT' => 'Italy',
		'JP' => 'Japan',
		'MX' => 'Mexico',
		'' => '--------------',
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'AP' => 'Azores',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegowina',
		'XB' => 'Bosnia-Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'VG' => 'British Virgin Islands',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, The Democratic Republic O',
		'CK' => 'Cook Islands',
		'XE' => 'Corsica',
		'CR' => 'Costa Rica',
		'CI' => 'Cote d` Ivoire (Ivory Coast)',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TP' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France (Includes Monaco)',
		'FX' => 'France, Metropolitan',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TA' => 'French Polynesia (Tahiti)',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard And Mc Donald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'EI' => 'Ireland (Eire)',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KP' => 'Korea, Democratic People\'S Repub',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'ME' => 'Madeira Islands',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States Of',
		'MD' => 'Moldova, Republic Of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar (Burma)',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'KN' => 'Saint Kitts And Nevis',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'XS' => 'Serbia-Montenegro',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovak Republic',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia And The South Sand',
		'KR' => 'South Korea',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'NV' => 'St. Christopher and Nevis',
		'SH' => 'St. Helena',
		'LC' => 'St. Lucia',
		'PM' => 'St. Pierre and Miquelon',
		'VC' => 'St. Vincent and the Grenadines',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard And Jan Mayen Islands',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'XU' => 'Tristan da Cunha',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'UK' => 'United Kingdom',
		'GB' => 'Great Britain',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Isl',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'XV' => 'Vatican City',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VI' => 'Virgin Islands (U.S.)',
		'WF' => 'Wallis and Furuna Islands',
		'EH' => 'Western Sahara',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'YU' => 'Yugoslavia',
		'ZR' => 'Zaire',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'	
	);

	$countries = array();
	foreach($country_names as $name => $value) 
		$countries[$name] = __($value);

	return $countries;


}

?>
