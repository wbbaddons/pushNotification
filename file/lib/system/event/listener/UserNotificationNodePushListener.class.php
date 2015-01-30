<?php
namespace wcf\system\event\listener;
use wcf\system\event\IEventListener;

/**
 * Sends notifications to users via nodePush
 * 
 * @author	Maximilian Mader
 * @copyright	2014 Maximilian Mader
 * @license	DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE Version 2, December 2004 <http://www.wtfpl.net/about/>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class UserNotificationNodePushListener implements IEventListener {
	/**
	 * @see	\wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj->getActionName() !== 'addRecipients' && $eventObj->getActionName() !== 'createStackable') return;
		
		$parameters = $eventObj->getParameters();
		
		$recipients = array_map(function($user) {
			return $user->userID;
		}, $parameters['recipients']);
		
		\wcf\system\push\PushHandler::getInstance()->sendMessage('be.bastelstu.max.wcf.user.newNotification', $recipients);
	}
}
