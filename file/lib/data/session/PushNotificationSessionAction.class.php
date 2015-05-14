<?php
namespace wcf\data\session;
use wcf\system\event\EventHandler;
use wcf\system\session\SessionHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Executes session-related actions. Duplicate of \wcf\data\session\SessionAction to always support keep alive requests.
 * Guest support was removed.
 *
 * @author	Magnus Kühn
 * @copyright	2014 Maximilian Mader
 * @license	DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE Version 2, December 2004 <http://www.wtfpl.net/about/>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class PushNotificationSessionAction extends SessionAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array();

	/**
	 * Updates session's last activity time to prevent it from expiring. In addition this method
	 * will return updated counters for notifications and 3rd party components.
	 *
	 * @return	array<mixed>
	 */
	public function keepAlive() {
		// update last activity time
		SessionHandler::getInstance()->keepAlive();

		// update notification counts
		$this->keepAliveData = array(
			'userNotificationCount' => UserNotificationHandler::getInstance()->getNotificationCount(true)
		);

		// notify 3rd party components
		EventHandler::getInstance()->fireAction($this, 'keepAlive');

		return $this->keepAliveData;
	}
}
