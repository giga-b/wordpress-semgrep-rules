<?php

namespace Voxel\Data\Mapbox;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Supported_Languages {

	// @link https://docs.mapbox.com/api/search/geocoding/#language-coverage
	public static function global_coverage() {
		return [
			'de' => 'German',
			'en' => 'English',
			'es' => 'Spanish',
			'fr' => 'French',
			'it' => 'Italian',
			'nl' => 'Dutch',
			'pl' => 'Polish',
		];
	}

	public static function local_coverage() {
		return [
			'az' => 'Azerbaijani',
			'bn' => 'Bengali',
			'ca' => 'Catalan',
			'cs' => 'Czech',
			'da' => 'Danish',
			'el' => 'Modern Greek',
			'fa' => 'Persian',
			'fi' => 'Finnish',
			'ga' => 'Irish',
			'hu' => 'Hungarian',
			'id' => 'Indonesian',
			'is' => 'Icelandic',
			'ja' => 'Japanese',
			'ka' => 'Georgian',
			'km' => 'Central Khmer',
			'ko' => 'Korean',
			'lt' => 'Lithuanian',
			'lv' => 'Latvian',
			'mn' => 'Mongolian',
			'pt' => 'Portuguese',
			'ro' => 'Romanian',
			'sk' => 'Slovak',
			'sq' => 'Albanian',
			'sv' => 'Swedish',
			'th' => 'Thai',
			'tl' => 'Tagalog',
			'uk' => 'Ukrainian',
			'vi' => 'Vietnamese',
			'zh' => 'Chinese',
			'zh_Hans' => 'Simplified Chinese',
			'zh_TW' => 'Taiwanese Mandarin',
		];
	}

	public static function limited_coverage() {
		return [
			'ar' => 'Arabic',
			'bs' => 'Bosnian',
			'gu' => 'Gujarati',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'kk' => 'Kazakh',
			'lo' => 'Lao',
			'my' => 'Burmese',
			'nb' => 'Norwegian Bokmål',
			'ru' => 'Russian',
			'sr' => 'Serbian',
			'te' => 'Telugu',
			'tk' => 'Turkmen',
			'tr' => 'Turkish',
			'zh_Hant' => 'Traditional Chinese',
		];
	}
}
