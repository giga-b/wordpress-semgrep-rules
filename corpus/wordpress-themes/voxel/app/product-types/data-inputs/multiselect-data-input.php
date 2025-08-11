<?php

namespace Voxel\Product_Types\Data_Inputs;

use \Voxel\Form_Models\Form_Models;
use \Voxel\Utils\Config_Schema\{Schema, Base_Data_Type, Data_Object};

if ( ! defined('ABSPATH') ) {
	exit;
}

class Multiselect_Data_Input extends Base_Data_Input {

	public function get_type(): string {
		return 'multiselect';
	}

	public function get_schema(): Data_Object {
		return Schema::Object( [
			'key' => Schema::String()->default( $this->get_type() ),
			'label' => Schema::String()->default( 'Multiselect' ),
			'placeholder' => Schema::String(),
			'required' => Schema::Bool()->default( false ),
			'display_mode' => Schema::Enum( [ 'buttons', 'checkboxes' ] )->default('checkboxes'),
			'choices' => Schema::Object_List( [
				'value' => Schema::String()->default(''),
				'label' => Schema::String()->default(''),
			] )->default([]),
		] );
	}

	public function get_controls(): array {
		return [
			Form_Models::Text( [
				'v-model' => 'dataInput.label',
				'label' => 'Label',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Key( [
				'v-model' => 'dataInput.key',
				'label' => 'Key',
				'ref' => 'keyInput',
				'classes' => 'x-col-6',
			] ),
			Form_Models::Select( [
				'v-model' => 'dataInput.display_mode',
				'label' => 'Display mode',
				'classes' => 'x-col-12',
				'choices' => [
					'buttons' => 'Buttons',
					'checkboxes' => 'Checkboxes',
				],
			] ),
			Form_Models::Text( [
				'v-model' => 'dataInput.placeholder',
				'label' => 'Placeholder',
				'classes' => 'x-col-12',
			] ),
			Form_Models::Switcher( [
				'v-model' => 'dataInput.required',
				'label' => 'Is required?',
				'classes' => 'x-col-12',
			] ),
			'choices' => Form_Models::Raw( function() { ?>
				<div class="ts-form-group x-col-12">
					<label>Choices</label>
					<data-input-select-choices :data-input="dataInput"></data-input-select-choices>
				</div>
			<?php } ),
		];
	}

	public function get_product_form_schema(): Base_Data_Type {
		$choices = $this->get_choices();
		return Schema::List()
			->unique()
			->validator( function( $item ) use ( $choices ) {
				return isset( $choices[ $item ] );
			} )
			->default([]);
	}

	public function get_product_form_frontend_props(): array {
		return [
			'placeholder' => $this->get_prop( 'placeholder' ),
			'choices' => $this->get_prop( 'choices' ),
			'display_mode' => $this->get_prop( 'display_mode' ),
		];
	}

	public function get_choices(): array {
		$choices = [];
		foreach ( $this->get_prop('choices') as $choice ) {
			$choices[ $choice['value'] ] = [
				'value' => $choice['value'],
				'label' => $choice['label'],
			];
		}

		return $choices;
	}

	public function validate_in_cart_item( $value ): void {
		if ( $this->is_required() && empty( $value ) ) {
			throw new \Exception( \Voxel\replace_vars( _x( '@input_name is required', 'data input', 'voxel' ), [
				'@input_name' => $this->get_label(),
			] ) );
		}
	}

	public function store_in_order_item( $value ): ?array {
		$choices = $this->get_choices();
		$selected = [];

		foreach ( $value as $item ) {
			if ( isset( $choices[ $item ] ) ) {
				$selected[ $item ] = $choices[ $item ];
			}
		}

		if ( empty( $selected ) ) {
			return null;
		}

		return [
			'type' => $this->get_type(),
			'label' => $this->get_label(),
			'value' => $selected,
		];
	}
}
