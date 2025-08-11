<?php

namespace Voxel\Membership\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Username_Field extends Base_Membership_Field {
	protected $props = [
		'key' => 'voxel:auth-username',
		'label' => 'Username',
	];
}
