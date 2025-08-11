<?php

namespace Voxel\Events\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Plan_Activated_Event extends \Voxel\Events\Base_Event {

	public $user, $membership;

	public function prepare( $user_id, $membership ) {
		$user = \Voxel\User::get( $user_id );
		if ( ! ( $user && $membership ) ) {
			throw new \Exception( 'User not found.' );
		}

		$this->user = $user;
		$this->membership = $membership;
	}

	public function get_key(): string {
		return 'membership/plan:activated';
	}

	public function get_label(): string {
		return 'Membership: Plan activated';
	}

	public function get_category() {
		return 'membership';
	}

	public static function notifications(): array {
		return [
			'user' => [
				'label' => 'Notify user',
				'recipient' => function( $event ) {
					return $event->user;
				},
				'inapp' => [
					'enabled' => false,
					'subject' => 'You have been assigned the @membership(plan.label) plan',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'membership' => $event->membership->get_details_for_app_event(),
						];
					},
					'apply_details' => function( $event, $details ) {
						if ( empty( $details['membership'] ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $details['user_id'] ?? null, \Voxel\Membership\Base_Type::create_from_details_for_app_event(
							(array) $details['membership']
						) );
					},
					'links_to' => function( $event ) { return get_permalink( \Voxel\get( 'templates.current_plan' ) ) ?: home_url('/'); },
				],
				'email' => [
					'enabled' => false,
					'subject' => 'You have been assigned the @membership(plan.label) plan',
					'message' => <<<HTML
					Your selected plan <strong>@membership(plan.label)</strong> has been assigned and activated.<br>
					Pricing: @membership(pricing.amount) @membership(pricing.period)
					<a href="@site(current_plan_url)">Open</a>
					HTML,
				],
			],
			'admin' => [
				'label' => 'Notify admin',
				'recipient' => function( $event ) {
					return \Voxel\User::get( \Voxel\get( 'settings.notifications.admin_user' ) );
				},
				'inapp' => [
					'enabled' => true,
					'subject' => '@user(:display_name) activated @membership(plan.label) plan: @membership(pricing.amount) @membership(pricing.period)',
					'details' => function( $event ) {
						return [
							'user_id' => $event->user->get_id(),
							'membership' => $event->membership->get_details_for_app_event(),
						];
					},
					'apply_details' => function( $event, $details ) {
						if ( empty( $details['membership'] ) ) {
							throw new \Exception( 'Missing data.' );
						}

						$event->prepare( $details['user_id'] ?? null, \Voxel\Membership\Base_Type::create_from_details_for_app_event(
							(array) $details['membership']
						) );
					},
					'links_to' => function( $event ) { return $event->user->get_link(); },
					'image_id' => function( $event ) { return $event->user->get_avatar_id(); },
				],
				'email' => [
					'enabled' => false,
					'subject' => '@user(:display_name) activated @membership(plan.label) plan: @membership(pricing.amount) @membership(pricing.period)',
					'message' => <<<HTML
					<strong>@user(:display_name)</strong> activated <strong>@membership(plan.label)</strong> plan.<br>
					Pricing: @membership(pricing.amount) @membership(pricing.period)
					<a href="@user(:profile_url)">Open</a>
					HTML,
				],
			],
		];
	}

	public function set_mock_props() {
		$this->user = \Voxel\User::mock();
		$this->membership = \Voxel\User::mock()->get_membership();
	}

	public function dynamic_tags(): array {
		return [
			'user' => \Voxel\Dynamic_Data\Group::User( $this->user ),
			'membership' => \Voxel\Dynamic_Data\Group::User_Membership( $this->membership ),
		];
	}
}
