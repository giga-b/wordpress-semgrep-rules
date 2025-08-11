<?php

namespace Voxel\Membership;

if ( ! defined('ABSPATH') ) {
	exit;
}

class Type_Default extends Base_Type {

	protected $type = 'default';

	protected $config;

	public function is_active() {
		return true;
	}

	protected function init( array $config ) {
		$this->config = $config;
	}

	// initial state, right after a user is registered
	// means the meta field for user plan has not been set yet
	public function is_initial_state() {
		return empty( $this->config );
	}
}
