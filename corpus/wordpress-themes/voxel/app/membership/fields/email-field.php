<?php

namespace Voxel\Membership\Fields;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Email_Field extends Base_Membership_Field  {
	protected $props = [
		'key' => 'voxel:auth-email',
		'label' => 'Email address',
	];
}
