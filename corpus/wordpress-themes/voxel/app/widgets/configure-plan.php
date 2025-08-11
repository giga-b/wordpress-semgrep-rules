<?php

namespace Voxel\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Configure_Plan extends Base_Widget {

	public function get_name() {
		return 'ts-configure-plan';
	}

	public function get_title() {
		return __( 'Configure plan (VX)', 'voxel-elementor' );
	}

	public function get_categories() {
		return [ 'voxel', 'basic' ];
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section( 'configure_icons', [
			'label' => __( 'Icons', 'voxel-elementor' ),
			'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

			$this->add_control( 'ts_info_icon', [
				'label' => __( 'Info icon', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
			] );

			$this->add_control( 'right_ico', [
				'label' => __( 'Right chevron', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
			] );

		$this->end_controls_section();

		$this->start_controls_section(
			'panel_options',
			[
				'label' => __( 'Panel', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'panel_border',
					'label' => __( 'Border', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel',
				]
			);


			$this->add_responsive_control(
				'panel_radius',
				[
					'label' => __( 'Border radius', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-panel' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'panel_bg',
				[
					'label' => __( 'Background', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel' => 'background: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'panel_shadow',
					'label' => __( 'Box Shadow', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel',
				]
			);

			$this->add_responsive_control(
				'head_border_col',
				[
					'label' => __( 'Separator color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head,{{WRAPPER}} .limit-warning' => 'border-color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'panel_head',
				[
					'label' => __( 'Panel head', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'head_padding',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .ac-head' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_size',
				[
					'label' => __( 'Icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head i' => 'font-size: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_margin',
				[
					'label' => __( 'Icon right margin', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head i' => 'margin-right: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'margin-right: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'head_ico_col',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head i' => 'color: {{VALUE}}',
						'{{WRAPPER}} .ts-panel .ac-head svg' => 'fill: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'head_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel .ac-head b',
				]
			);

			$this->add_responsive_control(
				'head_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-head b' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_control(
				'panel_limits',
				[
					'label' => __( 'Post limits', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'limits_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .config-plans .ac-head .ts-limits span',
				]
			);

			$this->add_responsive_control(
				'limits_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .config-plans .ac-head .ts-limits span' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'limits_typo_warn',
				[
					'label' => __( 'Text color (Warning)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .config-plans .ac-head .ts-limits span.limit-red' => 'color: {{VALUE}}',
					],

				]
			);


			$this->add_control(
				'panel_body',
				[
					'label' => __( 'Panel body', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);



			$this->add_responsive_control(
				'panel_spacing',
				[
					'label' => __( 'Padding', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ac-body,{{WRAPPER}} .limit-warning, {{WRAPPER}} .increase-limits' => 'padding: {{SIZE}}{{UNIT}};',
					],
				]
			);




			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'body_typo',
					'label' => __( 'Typography', 'voxel-elementor' ),
					'selector' => '{{WRAPPER}} .ts-panel .ac-body p',
				]
			);

			$this->add_responsive_control(
				'body_typo_col',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-panel .ac-body p' => 'color: {{VALUE}}',
					],

				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'ts_warning',
			[
				'label' => __( 'Limit warning', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'limit_gap',
				[
					'label' => __( 'Item gap', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .limit-warning' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'limit_icn_color',
				[
					'label' => __( 'Icon color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .limit-warning .info-ico svg' => 'fill: {{VALUE}}',
						'{{WRAPPER}} .limit-warning .info-ico i' => 'color: {{VALUE}}',
					],

				]
			);

			$this->add_responsive_control(
				'limit_icn_size',
				[
					'label' => __( 'Button icon size', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .limit-warning .info-ico svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .limit-warning .info-ico i' => 'font-size: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'limit_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .limit-warning p',
				]
			);

			$this->add_control(
				'limit_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .limit-warning p'
						=> 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'auth_primary_btn',
			[
				'label' => __( 'Primary button', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'one_btn_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'one_btn_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);



					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'one_btn_typo',
							'label' => __( 'Button typography', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);


					$this->add_responsive_control(
						'one_btn_radius',
						[
							'label' => __( 'Border radius', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'default' => [
								'unit' => 'px',
								'size' => 5,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'one_btn_c',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'one_btn_padding',
						[
							'label' => __( 'Padding', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em' ],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'one_btn_height',
						[
							'label' => __( 'Height', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'height: {{SIZE}}{{UNIT}};',
							],
						]
					);


					$this->add_responsive_control(
						'one_btn_bg',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'one_btn_border',
							'label' => __( 'Border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-btn-2',
						]
					);


					$this->add_responsive_control(
						'one_btn_icon_size',
						[
							'label' => __( 'Icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2 i' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-btn-2 svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'one_btn_icon_pad',
						[
							'label' => __( 'Icon/Text spacing', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2' => 'grid-gap: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'one_btn_icon_color',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2 i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2 svg' => 'fill: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'one_btn_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_responsive_control(
						'one_btn_c_h',
						[
							'label' => __( 'Text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'one_btn_bg_h',
						[
							'label' => __( 'Background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'background: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'one_btn_border_h',
						[
							'label' => __( 'Border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover' => 'border-color: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'one_btn_icon_color_h',
						[
							'label' => __( 'Icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-btn-2:hover i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-btn-2:hover svg' => 'fill: {{VALUE}}',
							],

						]
					);



				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'ts_sf_stepper',
			[
				'label' => __( 'Form: Number stepper', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs(
				'ts_stepper_tabs'
			);

				/* Normal tab */

				$this->start_controls_tab(
					'ts_stepper_normal',
					[
						'label' => __( 'Normal', 'voxel-elementor' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' => 'label_typo',
							'label' => __( 'Label typography' ),
							'selector' => '{{WRAPPER}} .limit-info p',
						]
					);

					$this->add_control(
						'ts_label_color',
						[
							'label' => __( 'Label text color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .limit-info p'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'popup_number_input_size',
						[
							'label' => __( 'Value size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px'],
							'range' => [
								'px' => [
									'min' => 13,
									'max' => 30,
									'step' => 1,
								],
							],
							'default' => [
								'unit' => 'px',
								'size' => 20,
							],
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input input' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_value_color',
						[
							'label' => __( 'Value color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input input'
								=> 'color: {{VALUE}}',
							],

						]
					);

					$this->add_control(
						'ts_stepper_btn_color',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button'
								=> 'color: {{VALUE}}',
								'{{WRAPPER}} .ts-stepper-input button svg'
								=> 'fill: {{VALUE}}',
							],

						]
					);

					$this->add_responsive_control(
						'ts_stepper_btn_icon_size',
						[
							'label' => __( 'Button icon size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button' => 'font-size: {{SIZE}}{{UNIT}};',
								'{{WRAPPER}} .ts-stepper-input button svg' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'ts_stepper_btn_bg',
						[
							'label' => __( 'Button background', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button'
								=> 'background-color: {{VALUE}}',
							],

						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'ts_stepper_btn_border',
							'label' => __( 'Button border', 'voxel-elementor' ),
							'selector' => '{{WRAPPER}} .ts-stepper-input button',
						]
					);

					$this->add_responsive_control(
						'ts_stepper_btn_radius',
						[
							'label' => __( 'Button border radius', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button' => 'border-radius: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'ts_stepper_btn_size',
						[
							'label' => __( 'Button size', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'size_units' => [ 'px', '%' ],
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 100,
									'step' => 1,
								],
								'%' => [
									'min' => 0,
									'max' => 100,
								],
							],
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
							],
						]
					);

				$this->end_controls_tab();


				/* Hover tab */

				$this->start_controls_tab(
					'ts_stepper_hover',
					[
						'label' => __( 'Hover', 'voxel-elementor' ),
					]
				);

					$this->add_control(
						'ts_stepper_btn_h',
						[
							'label' => __( 'Button icon color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button:hover'
								=> 'color: {{VALUE}};',
								'{{WRAPPER}} .ts-stepper-input button:hover svg'
								=> 'fill: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_stepper_btn_bg_h',
						[
							'label' => __( 'Button background color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button:hover'
								=> 'background-color: {{VALUE}};',
							],

						]
					);

					$this->add_control(
						'ts_stepper_border_c_h',
						[
							'label' => __( 'Button border color', 'voxel-elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ts-stepper-input button:hover'
								=> 'border-color: {{VALUE}};',
							],

						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();



		$this->end_controls_section();

		$this->start_controls_section(
			'prform_calculator',
			[
				'label' => __( 'Form: Price calculator', 'voxel-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'calc_list_gap',
				[
					'label' => __( 'List spacing', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator' => 'grid-gap: {{SIZE}}{{UNIT}};',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'calc_text',
					'label' => __( 'Typography' ),
					'selector' => '{{WRAPPER}} .ts-cost-calculator li p',
				]
			);

			$this->add_control(
				'calc_text_color',
				[
					'label' => __( 'Text color', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator li p'
						=> 'color: {{VALUE}}',
					],

				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'calc_text_total',
					'label' => __( 'Typography (Total)' ),
					'selector' => '{{WRAPPER}} .ts-cost-calculator li.ts-total p',
				]
			);

			$this->add_control(
				'calc_text_color_total',
				[
					'label' => __( 'Text color (Total)', 'voxel-elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ts-cost-calculator li.ts-total p'
						=> 'color: {{VALUE}}',
					],

				]
			);

		$this->end_controls_section();
	}

	protected function render( $instance = [] ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_print_styles( $this->get_style_depends() );

		// dump(json_decode( get_user_meta( get_current_user_id(), 'voxel:test_plan', true ), true ) );

		if ( isset( $_GET['price'] ) && $_GET['price'] !== 'current' ) {
			$this->_modify_new_plan();
		} else {
			$this->_modify_current_plan();
		}

		if ( \Voxel\is_edit_mode() ) {
			printf( '<script type="text/javascript">%s</script>', 'window.render_configure_plan();' );
		}
	}

	protected function _modify_new_plan() {
		$user = \Voxel\current_user();

		try {
			if ( $_GET['price'] === 'default' ) {
				$this->_modify_default_plan( \Voxel\Plan::get_or_create_default_plan(), false );
				return;
			}

			$price = \Voxel\Plan_Price::from_key( sanitize_text_field( $_GET['price'] ) );
			if ( ! $price->is_enabled() ) {
				throw new \Exception( 'This plan is not active.' );
			}

			if ( ! in_array( $price->get_type(), [ 'one_time', 'recurring' ], true ) ) {
				throw new \Exception( 'Cannot modify this plan.' );
			}

			// @todo validate plan is enabled for current user role
		} catch ( \Exception $e ) {
			if ( \Voxel\is_dev_mode() ) {
				// dump($e->getMessage());
			}

			echo sprintf( '<p>%s</p>', _x( 'This plan cannot be purchased.', 'pricing', 'voxel' ) );
			return;
		}

		$plan = $price->plan;

		if ( $price->get_type() === 'recurring' ) {
			$details = $price->get_details();

			$config = [
				'is_current_plan' => false,
				'_wpnonce' => wp_create_nonce( 'vx_modify_plan' ),
				'redirect_to' => $_GET['redirect_to'] ?? null,
				'price' => [
					'type' => 'subscription',
					'id' => $price->get_id(),
					'key' => $price->to_key(),
					'amount' => $price->get_amount(),
					'currency' => $price->get_currency(),
					'is_zero_decimal' => \Voxel\Stripe\Currencies::is_zero_decimal( $price->get_currency() ),
				],
				'plan' => [
					'key' => $plan->get_key(),
					'label' => $plan->get_label(),
				],
				'post_types' => $this->_get_post_types_for_submission( $user, $plan, $price ),
				'l10n' => [
					'interval' => \Voxel\interval_format( $details['recurring']['interval'] ?? '', $details['recurring']['interval_count'] ?? '' ),
				],
			];

			require locate_template( 'templates/widgets/configure-plan.php' );
		} elseif ( $price->get_type() === 'one_time' ) {
			$config = [
				'is_current_plan' => false,
				'_wpnonce' => wp_create_nonce( 'vx_modify_plan' ),
				'redirect_to' => $_GET['redirect_to'] ?? null,
				'price' => [
					'type' => 'payment',
					'id' => $price->get_id(),
					'key' => $price->to_key(),
					'amount' => $price->get_amount(),
					'currency' => $price->get_currency(),
					'is_zero_decimal' => \Voxel\Stripe\Currencies::is_zero_decimal( $price->get_currency() ),
				],
				'plan' => [
					'key' => $plan->get_key(),
					'label' => $plan->get_label(),
				],
				'post_types' => $this->_get_post_types_for_submission( $user, $plan, $price ),
			];

			require locate_template( 'templates/widgets/configure-plan.php' );
		}
	}

	protected function _modify_current_plan() {
		$user = \Voxel\current_user();

		try {
			$membership = $user->get_membership();
			if ( $membership->get_type() === 'default' ) {
				$this->_modify_default_plan( $membership->plan, true );
				return;
			}

			if ( ! $membership->is_active() ) {
				throw new \Exception( 'Cannot modify current plan.' );
			}

			if ( ! in_array( $membership->get_type(), [ 'payment', 'subscription' ], true ) ) {
				throw new \Exception( 'Cannot modify current plan.' );
			}

			$plan = $membership->plan;
			$price = new \Voxel\Plan_Price( [
				'id' => $membership->get_price_id(),
				'mode' => \Voxel\Stripe::is_test_mode() ? 'test' : 'live',
				'plan' => $plan->get_key(),
			] );
		} catch ( \Exception $e ) {
			if ( \Voxel\is_dev_mode() ) {
				// dump($e->getMessage());
			}

			echo sprintf( '<p>%s</p>', _x( 'This plan cannot be modified.', 'pricing', 'voxel' ) );
			return;
		}

		if ( $membership->get_type() === 'subscription'  ) {
			$config = [
				'is_current_plan' => true,
				'_wpnonce' => wp_create_nonce( 'vx_modify_plan' ),
				'redirect_to' => $_GET['redirect_to'] ?? null,
				'price' => [
					'type' => 'subscription',
					'id' => $price->get_id(),
					'key' => $price->to_key(),
					'amount' => $membership->get_amount(),
					'currency' => $membership->get_currency(),
					'is_zero_decimal' => \Voxel\Stripe\Currencies::is_zero_decimal( $membership->get_currency() ),
				],
				'plan' => [
					'key' => $plan->get_key(),
					'label' => $plan->get_label(),
				],
				'post_types' => $this->_get_post_types_for_submission( $user, $plan, $price ),
				'l10n' => [
					'interval' => \Voxel\interval_format( $membership->get_interval(), $membership->get_interval_count() ),
				],
			];

			require locate_template( 'templates/widgets/configure-plan.php' );
		} elseif ( $membership->get_type() === 'payment' ) {
			$config = [
				'is_current_plan' => true,
				'_wpnonce' => wp_create_nonce( 'vx_modify_plan' ),
				'redirect_to' => $_GET['redirect_to'] ?? null,
				'price' => [
					'type' => 'payment',
					'id' => $price->get_id(),
					'key' => $price->to_key(),
					'amount' => $membership->get_amount(),
					'currency' => $membership->get_currency(),
					'is_zero_decimal' => \Voxel\Stripe\Currencies::is_zero_decimal( $membership->get_currency() ),
				],
				'plan' => [
					'key' => $plan->get_key(),
					'label' => $plan->get_label(),
				],
				'post_types' => $this->_get_post_types_for_submission( $user, $plan, $price ),
			];

			require locate_template( 'templates/widgets/configure-plan.php' );
		}
	}

	protected function _modify_default_plan( $plan, $is_current_plan ) {
		$user = \Voxel\current_user();
		$config = [
			'is_current_plan' => $is_current_plan,
			'_wpnonce' => wp_create_nonce( 'vx_modify_plan' ),
			'redirect_to' => $_GET['redirect_to'] ?? null,
			'price' => [
				'type' => 'default',
				'key' => 'default',
			],
			'plan' => [
				'key' => $plan->get_key(),
				'label' => $plan->get_label(),
			],
			'post_types' => $this->_get_post_types_for_submission( $user, $plan, null ),
		];

		require locate_template( 'templates/widgets/configure-plan.php' );
	}

	protected function _get_post_types_for_submission( \Voxel\User $user, \Voxel\Plan $plan, \Voxel\Plan_Price $price = null ) {
		$post_types = [];
		foreach ( $plan->get_submission_limits() as $post_type_key => $limit_config ) {
			$post_type = \Voxel\Post_Type::get( $post_type_key );
			if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
				continue;
			}

			$limit = new \Voxel\Submission_Limit( $user, $post_type, $limit_config, $plan );

			$post_types[ $post_type->get_key() ] = [
				'key' => $post_type->get_key(),
				'label' => $post_type->get_label(),
				'icon' => \Voxel\get_icon_markup( $post_type->get_icon() ),
				'current_count' => $limit->get_submission_count(),
				'base_limit' => $limit->get_count(),
				'supports_addition' => $price ? $price->supports_addition( $post_type->get_key() ) : false,
				'price_per_addition' => $price ? $price->get_price_per_addition( $post_type->get_key() ) : null,
				'additional_items' => 0,
				'current_limit' => 0,
			];
		}

		$stats = $user->get_post_stats();
		foreach ( $stats as $post_type_key => $post_type_stats ) {
			if ( isset( $post_types[ $post_type_key ] ) ) {
				continue;
			}

			if ( ( $post_type_stats['publish'] ?? 0 ) < 1 ) {
				continue;
			}

			if ( in_array( $post_type_key, [ 'profile' ], true ) ) {
				continue;
			}

			$post_type = \Voxel\Post_Type::get( $post_type_key );
			if ( ! ( $post_type && $post_type->is_managed_by_voxel() ) ) {
				continue;
			}

			$post_types[ $post_type->get_key() ] = [
				'key' => $post_type->get_key(),
				'label' => $post_type->get_label(),
				'icon' => \Voxel\get_icon_markup( $post_type->get_icon() ),
				'current_count' => $post_type_stats['publish'] ?? 0,
				'base_limit' => 0,
				'supports_addition' => false,
				'price_per_addition' => null,
				'additional_items' => 0,
				'current_limit' => 0,
			];
		}

		foreach ( (array) $user->get_submission_limit_for_all_post_types() as $post_type_key => $limit ) {
			if ( isset( $post_types[ $post_type_key ] ) ) {
				$post_types[ $post_type_key ]['current_limit'] = $limit->get_count();
			}
		}

		return $post_types;
	}

	public function get_script_depends() {
		return [
			'vx:configure-plan.js',
		];
	}

	public function get_style_depends() {
		return [ 'vx:forms.css', 'vx:configure-plan.css' ];
	}


	protected function content_template() {}
	public function render_plain_content( $instance = [] ) {}
}

