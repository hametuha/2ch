<?php

namespace Hametuha\Nichan;

use Hametuha\Nichan\Admin\SettingScreen;
use Hametuha\Nichan\API\Comment;
use Hametuha\Nichan\API\Thread;
use Hametuha\Nichan\Pattern\Singleton;

/**
 * Bootstrap Class
 *
 * @package Hametuha\Nichan
 */
class Bootstrap extends Singleton {

	/**
	 * Just call controllers.
	 */
	protected function initialize() {
		SettingScreen::instance();
		Thread::instance();
		Comment::instance();
	}
}
