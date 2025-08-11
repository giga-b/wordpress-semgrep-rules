<?php

namespace Voxel\Direct_Messages;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Content_Field extends \Voxel\Object_Fields\Base_Field {

	protected function base_props(): array {
		return [
			'label' => 'Content',
			'key' => 'content',
			'maxlength' => \Voxel\get( 'settings.messages.maxlength', 2000 ),
		];
	}

	public function sanitize( $value ) {
		return sanitize_textarea_field( $value );
	}

	public function validate( $value ): void {
		$this->validate_maxlength( $value );
	}
}
