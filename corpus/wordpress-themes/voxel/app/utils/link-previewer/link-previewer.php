<?php

namespace Voxel\Utils\Link_Previewer;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Link_Previewer {

	protected static $_instance = null;

	public static function get(): self {
		return new static;
	}

	public function preview( string $url ): ?array {
		$request = $this->fetch_url( $url );
		if ( $request === null ) {
			return null;
		}

		$data = $this->get_data_from_markup( $request['body'] );
		if ( $data['title'] === null ) {
			return null;
		}

		$data['url'] = $request['url'];
		return $data;
	}

	public function fetch_url( string $url ): ?array {
		$url = filter_var( $url, FILTER_SANITIZE_URL );
		if ( $url === false || empty( $url ) ) {
			return null;
		}

		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			return null;
		}

		$request = wp_safe_remote_get( $url, [
			'limit_response_size' => 2 * MB_IN_BYTES,
			'timeout' => 4, // seconds
			'redirection' => 2,
		] );

		$code = wp_remote_retrieve_response_code( $request );
		if ( $code !== 200 ) {
			return null;
		}

		return [
			'url' => $url,
			'body' => wp_remote_retrieve_body( $request ),
		];
	}

	public function get_data_from_markup( string $html_markup ): array {
		$meta_tags = join( '|', [
			'og:title',
			'twitter:title',
			'og:image',
			'twitter:image',
			// 'og:description',
			// 'twitter:description',
		] );

		preg_match_all(
			'/<meta(.*)(?:name|property)=(?:\"|\')(?<property>'.$meta_tags.')(?:\"|\')(.*?)(content=(?:\"|\')(?<content>[\s\S]+?)(?:\"|\'))(?:.*?)>/i',
			$html_markup,
			$matches
		);

		$title = null;
		$image = null;
		// $description = null;

		foreach ( $matches['property'] as $i => $property ) {
			if ( $title !== null && $image !== null ) {
				break;
			}

			if ( $i > 30 ) {
				break;
			}

			$content = $matches['content'][ $i ] ?? null;
			if ( ! is_string( $content ) || empty( $content ) ) {
				continue;
			}

			if ( in_array( $property, ['og:title', 'twitter:title' ], true ) && $title === null ) {
				$title = mb_substr( $content, 0, 200 );
				continue;
			}

			if ( in_array( $property, ['og:image', 'twitter:image' ], true ) && $image === null ) {
				$image = mb_substr( $content, 0, 500 );
				continue;
			}

			// if ( in_array( $property, ['og:description', 'twitter:description' ], true ) && $description === null ) {
			// 	$description = mb_substr( $content, 0, 500 );
			// 	continue;
			// }
		}

		if ( $title === null ) {
			preg_match("/<title(?:.*?)>(?<title>.+)<\/title>/i", $html_markup, $matches);

			if ( ! empty( $matches['title'] ) && is_string( $matches['title'] ) ) {
				$title = mb_substr( $matches['title'], 0, 200 );
			}
		}

		return [
			'title' => $title,
			'image' => $image,
			// 'description' => $description,
		];
	}

}
