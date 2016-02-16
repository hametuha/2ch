<?php

namespace Hametuha\Nichan;

use Hametuha\Nichan\Admin\SettingScreen;
use Hametuha\Nichan\Pattern\Singleton;
use Hametuha\Nichan\API\Thread;

/**
 * Bootstrap Class
 *
 * @package Hametuha\Nichan
 */
class Bootstrap extends Singleton {

	/**
	 * Executed on initialization
	 */
	protected function initialize() {
		SettingScreen::instance();
		Thread::instance();
	}



}
