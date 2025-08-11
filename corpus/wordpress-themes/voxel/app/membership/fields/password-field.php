<?php

namespace Voxel\Membership\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Password_Field extends Base_Membership_Field  {
	protected $props = [
		'key' => 'voxel:auth-password',
		'label' => 'Password',
	];
}
