<?php

namespace Voxel\Post_Types\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

abstract class Base_Post_Field extends \Voxel\Object_Fields\Base_Field {
	use Traits\Model_Helpers;
	use Traits\Validation_Helpers;

	/**
	 * Slugified string used to identify a field. Alias of `$this->props['key']`
	 *
	 * @since 1.0
	 */
	protected $key;

	/**
	 * Post object which this field belongs to.
	 *
	 * @since 1.0
	 */
	protected $post;

	/**
	 * Repeater object which this field belongs to.
	 *
	 * @since 1.0
	 */
	protected $repeater;

	/**
	 * Row index within repeater.
	 *
	 * @since 1.0
	 */
	protected $repeater_index = 0;

	/**
	 * Post type object which this field belongs to.
	 *
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * Instantiated field conditions.
	 *
	 * @since 1.0
	 */
	protected $conditions;

	/**
	 * Step key that this field belongs to.
	 *
	 * @since 1.0
	 */
	protected $step;

	protected $supported_conditions;

	protected function base_props(): array {
		return [
			'type' => 'text',
			'key' => 'custom-field',
			'label' => 'Custom Field',
			'description' => '',
			'required' => false,
			'hidden' => false,
			'enable-conditions' => false,
			'conditions_behavior' => 'show',
			'conditions' => [],
			'visibility_behavior' => 'show',
			'visibility_rules' => [],
			'css_class' => '',
		];
	}

	public static function preset( $props = [] ) {
		$props = ( new static( $props ) )->get_props();
		$props['singular'] = true;
		$props['_is_preset'] = true;
		return $props;
	}

	public static function is_repeatable(): bool {
		return true;
	}

	public static function is_singular(): bool {
		return false;
	}

	public function get_value() {
		if ( static::is_repeatable() && ! is_null( $this->repeater ) ) {
			return $this->get_value_from_repeater();
		}

		if ( $this->post ) {
			return $this->get_value_from_post();
		}

		return null;
	}

	public function get_value_from_post() {
		return null;
	}

	public function get_value_from_repeater() {
		$value = $this->repeater->get_value();
		if ( $value === null ) {
			return null;
		}

		return $value[ $this->repeater_index ][ $this->get_key() ] ?? null;
	}

	public function update_value_in_repeater( $value ) {
		return ! $this->is_empty( $value ) ? $value : null;
	}

	public function get_step() {
		return $this->step;
	}

	public function is_ui() {
		return false;
	}

	public function get_conditions() {
		if ( ! is_null( $this->conditions ) ) {
			return $this->conditions;
		}

		$condition_types = \Voxel\config('post_types.condition_types');
		$this->conditions = [];
		foreach ( (array) $this->props['conditions'] as $condition_group ) {
			$group = [];
			foreach ( (array) $condition_group as $condition_data ) {
				if ( empty( $condition_data['source'] ) || empty( $condition_data['type'] ) ) {
					continue;
				}

				if ( ! isset( $condition_types[ $condition_data['type'] ] ) ) {
					continue;
				}

				$condition = new $condition_types[ $condition_data['type'] ]( $condition_data );
				$condition->set_field( $this );
				$condition->set_post_type( $this->post_type );

				$group[] = $condition;
			}

			if ( ! empty( $group ) ) {
				$this->conditions[] = $group;
			}
		}

		return $this->conditions;
	}

	protected function frontend_conditions_config() {
		if ( ! $this->props['enable-conditions'] ) {
			return null;
		}

		$all_conditions = $this->get_conditions();
		$fields = ! is_null( $this->repeater )
			? $this->repeater->get_fields()
			: $this->post_type->get_fields();
		$config = [];

		foreach ( $this->get_conditions() as $condition_group ) {
			$group = [];

			foreach ( $condition_group as $condition ) {
				$group[] = array_merge( $condition->get_props(), [
					'source' => $condition->get_source(),
					'type' => $condition->get_type(),
					'_passes' => true,
				] );
			}

			if ( ! empty( $group ) ) {
				$config[] = $group;
			}
		}

		return $config;
	}

	public function get_path() {
		$path = ! is_null( $this->repeater )
			? $this->repeater->get_path().'.'.$this->get_key()
			: $this->get_key();

		return $path;
	}

	public function get_id() {
		return sprintf( '%s.%s', $this->post_type->get_key(), $this->get_path() );
	}

	public function get_frontend_config() {
		return [
			'id' => $this->get_id(),
			'type' => $this->get_type(),
			'key' => $this->get_key(),
			'label' => $this->get_label(),
			'description' => $this->get_description(),
			'required' => $this->is_required(),
			'hidden' => $this->is_hidden(),
			'css_class' => $this->props['css_class'],
			'props' => $this->frontend_props(),
			'conditions' => $this->frontend_conditions_config(),
			'conditions_behavior' => $this->get_prop('conditions_behavior') === 'hide' ? 'hide' : 'show',
			'step' => $this->get_step(),
			'is_ui' => $this->is_ui(),
			'value' => ! $this->is_ui() ? $this->editing_value() : null,
			'in_repeater' => $this->repeater !== null,
			'path' => $this->repeater !== null ? $this->repeater->get_path() : null,
			'validation' => [
				'errors' => [],
			],
		];
	}

	protected function frontend_props() {
		return [];
	}

	public function check_dependencies() {
		//
	}

	public function is_hidden() {
		return (bool) $this->props['hidden'];
	}

	protected function editing_value() {
		return $this->post ? $this->get_value() : null;
	}

	public function get_post() {
		return $this->post;
	}

	public function set_post( \Voxel\Post $post ) {
		$this->post = $post;
	}

	public function set_post_type( \Voxel\Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	public function get_post_type() {
		return $this->post_type;
	}

	public function set_repeater( Repeater_Field $repeater ) {
		$this->repeater = $repeater;
	}

	public function set_repeater_index( $repeater_index ) {
		$this->repeater_index = $repeater_index;
	}

	public function get_repeater() {
		return $this->repeater;
	}

	public function get_repeater_index() {
		return $this->repeater_index;
	}

	public function set_step( string $step_key ) {
		$this->step = $step_key;
	}

	// @deprecated since 1.5
	public function exports() {
		return null;
	}

	public function dynamic_data() {
		return null;
	}

	public function get_supported_conditions() {
		return $this->supported_conditions;
	}

	public function passes_visibility_rules(): bool {
		$behavior = $this->props['visibility_behavior'];
		$rules = $this->props['visibility_rules'];

		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return true;
		}

		// make sure visibility rules use the post this field belongs to during evaluation
		$original_post = \Voxel\get_current_post();
		if ( $this->post ) {
			\Voxel\set_current_post( $this->post );
		} else {
			\Voxel\set_current_post( \Voxel\Post::dummy( [
				'post_type' => $this->post_type->get_key(),
			] ) );
		}

		$rules_passed = \Voxel\evaluate_visibility_rules( $rules );

		// revert to original post
		if ( $original_post ) {
			\Voxel\set_current_post( $original_post );
		}

		if ( $behavior === 'hide' ) {
			return $rules_passed ? false : true;
		} else {
			return $rules_passed ? true : false;
		}
	}

	public function passes_conditional_logic( array $values ): bool {
		if ( $this->get_prop('enable-conditions') ) {
			$fields = ! is_null( $this->repeater ) ? $this->repeater->get_fields() : $this->post_type->get_fields();
			$conditions = $this->get_conditions();
			if ( empty( $conditions ) ) {
				return true;
			}

			$behavior = $this->get_prop('conditions_behavior');
			$passes_conditions = false;

			foreach ( $conditions as $condition_group ) {
				if ( empty( $condition_group ) ) {
					continue;
				}

				$group_passes = true;
				foreach ( $condition_group as $condition ) {
					$subject_parts = explode( '.', $condition->get_source() );
					$subject_field_key = $subject_parts[0];
					$subject_subfield_key = $subject_parts[1] ?? null;

					$subject_field = $fields[ $subject_field_key ] ?? null;
					if ( ! $subject_field ) {
						$group_passes = false;
					} else {
						$value = $values[ $subject_field->get_key() ];
						if ( $subject_subfield_key !== null ) {
							$value = $value[ $subject_subfield_key ] ?? null;
						}

						if ( $condition->evaluate( $value ) === false ) {
							$group_passes = false;
						}
					}
				}

				if ( $group_passes ) {
					$passes_conditions = true;
				}
			}

			if ( $behavior === 'hide' ) {
				return ! $passes_conditions;
			} else {
				return $passes_conditions;
			}
		} else {
			return true;
		}
	}

	public function get_value_for_personal_data_exporter() {
		// don't export fields with visibility rules set to avoid
		// leaking admin only fields to the post author
		$rules = $this->props['visibility_rules'];
		if ( is_array( $rules ) && ! empty( $rules ) ) {
			return null;
		}

		return $this->export_to_personal_data();
	}

	public function export_to_personal_data() {
		return null;
	}

	/**
	 * Sanitize field props when edited through the backend post type editor.
	 *
	 * @since 1.2.9
	 */
	public function sanitize_in_editor( $props ) {
		return $props;
	}

	public function is_new_post(): bool {
		if ( ! $this->post ) {
			return true;
		}

		if ( $this->post->get_status() === 'auto-draft' && ! wp_is_post_revision( $this->post->get_id() ) ) {
			return true;
		}

		return false;
	}
}
