<?php

namespace Voxel\Post_Types\Fields;

use \Voxel\Form_Models;
use \Voxel\Dynamic_Data\Tag as Tag;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Texteditor_Field extends Base_Post_Field {

	protected $supported_conditions = ['text'];

	protected $props = [
		'type' => 'texteditor',
		'label' => 'Text Editor',
		'placeholder' => '',
		'editor-type' => 'plain-text',
		'minlength' => null,
		'maxlength' => null,
		'default' => null,
	];

	public function get_models(): array {
		return [
			'label' => $this->get_label_model(),
			'key' => $this->get_key_model(),
			'placeholder' => $this->get_placeholder_model(),
			'editor-type' => $this->get_model( 'editor_type', [ 'classes' => 'x-col-6', ] ),
			'minlength' => $this->get_minlength_model(),
			'maxlength' => $this->get_maxlength_model(),
			'description' => $this->get_description_model(),
			'required' => $this->get_required_model(),
			'css_class' => $this->get_css_class_model(),
			'default' => $this->get_default_value_model(),
			'hidden' => $this->get_hidden_model(),
		];
	}

	public function sanitize( $value ) {
		return $this->props['editor-type'] === 'plain-text'
			? sanitize_textarea_field( trim( $value ) )
			: wp_kses_post( trim( $value ) );
	}

	public function validate( $value ): void {
		$strip_tags = $this->props['editor-type'] !== 'plain-text';
		$this->validate_minlength( $value, $strip_tags );
		$this->validate_maxlength( $value, $strip_tags );
	}

	public function update( $value ): void {
		if ( $this->is_empty( $value ) ) {
			delete_post_meta( $this->post->get_id(), $this->get_key() );
		} else {
			update_post_meta( $this->post->get_id(), $this->get_key(), wp_slash( $value ) );
		}
	}

	public function get_value_from_post() {
		return get_post_meta( $this->post->get_id(), $this->get_key(), true );
	}

	private function _get_editor_config() {
		if ( $this->get_prop('editor-type') === 'plain-text' ) {
			return [];
		}

		$config = [
			'textarea_name' => $this->_get_editor_id(),
			'textarea_rows' => 8,
			'tinymce' => [
				'fixed_toolbar_container' => sprintf( '#_toolbar-%s', $this->_get_editor_id() ),
				'paste_as_text' => true,
				'paste_auto_cleanup_on_paste' => true,
				'paste_remove_spans' => true,
				'paste_remove_styles' => true,
				'paste_remove_styles_if_webkit' => true,
				'paste_strip_class_attributes' => true,
				'wpautop' => true,
				'autoresize_min_height' => 150,
				'autoresize_max_height' => 800,
				'wp_autoresize_on' => true,
				'content_style' => <<<CSS
					body > :first-child { margin-top: 0; }
					body > :last-child { margin-bottom: 0; }
					a[data-wplink-url-error], a[data-wplink-url-error]:hover, a[data-wplink-url-error]:focus {
						outline: none;
					}
				CSS,
			],
		];

		// basic controls
		if ( $this->get_prop('editor-type') === 'wp-editor-basic' ) {
			$config['media_buttons'] = false;
			$config['quicktags'] = false;
			$config['tinymce']['plugins'] = 'lists,paste,tabfocus,wplink,wordpress,wpautoresize';
			$config['tinymce']['toolbar1'] = 'bold,italic,bullist,numlist,link,unlink';
		}

		// advanced controls
		if ( $this->get_prop('editor-type') === 'wp-editor-advanced' ) {
			$config['media_buttons'] = false;
			$config['quicktags'] = false;
			$tb = 'formatselect,bold,italic,bullist,numlist,link,unlink,strikethrough,alignleft,aligncenter,alignright,underline,hr';
			$config['tinymce']['toolbar1'] = $tb;
			$config['tinymce']['plugins'] = 'lists,paste,tabfocus,wplink,wordpress,colorpicker,hr,wpautoresize';
		}

		$config = apply_filters( 'voxel/texteditor-field/tinymce/config', $config, $this );

		return $config;
	}

	protected function frontend_props() {
		if ( $this->props['editor-type'] !== 'plain-text' ) {
			if ( ! class_exists( '_WP_Editors', false ) ) {
				require( ABSPATH . WPINC . '/class-wp-editor.php' );
			}

			wp_deregister_style( 'editor-buttons' );
			\_WP_Editors::enqueue_default_editor();
		}

		return [
			'editorId' => $this->_get_editor_id(),
			'toolbarId' => sprintf( '_toolbar-%s', $this->_get_editor_id() ),
			'placeholder' => $this->props['placeholder'] ?: $this->props['label'],
			'minlength' => is_numeric( $this->props['minlength'] ) ? absint( $this->props['minlength'] ) : null,
			'maxlength' => is_numeric( $this->props['maxlength'] ) ? absint( $this->props['maxlength'] ) : null,
			'editorType' => $this->props['editor-type'],
			'editorConfig' => $this->_get_editor_config(),
		];
	}

	protected function editing_value() {
		if ( $this->is_new_post() ) {
			return $this->render_default_value( $this->get_prop('default') );
		} else {
			if ( $this->props['editor-type'] === 'plain-text' ) {
				return $this->get_value();
			} else {
				return wpautop( (string) $this->get_value() );
			}
		}
	}

	protected function _get_editor_id() {
		if ( $this->repeater === null && $this->get_key() === 'description' ) {
			return 'content';
		}

		return str_replace( ' ', '_', str_replace( '.', '-', $this->get_id() ) );
	}

	public function dynamic_data() {
		return Tag::String( $this->get_label() )->render( function() {
			if ( $this->props['editor-type'] === 'plain-text' ) {
				return $this->get_value();
			} else {
				return wpautop( (string) $this->get_value() );
			}
		} );
	}

	public function export_to_personal_data() {
		if ( $this->props['editor-type'] === 'plain-text' ) {
			return $this->get_value();
		} else {
			return wpautop( $this->get_value() );
		}
	}
}
