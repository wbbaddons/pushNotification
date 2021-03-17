<?php
/*
 * Copyright © 2014 Maximilian Mader <max@bastelstu.be>
 * This work is free. You can redistribute it and/or modify it under the
 * terms of the Do What The Fuck You Want To Public License, Version 2,
 * as published by Sam Hocevar.
 *
 * ---------------------------------------------------------------------
 *
 *             DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 *                     Version 2, December 2004
 *
 *  Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>
 *
 *  Everyone is permitted to copy and distribute verbatim or modified
 *  copies of this license document, and changing it is allowed as long
 *  as the name is changed.
 *
 *             DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
 *    TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
 *
 *   0. You just DO WHAT THE FUCK YOU WANT TO.
 */

namespace wcf\system\event\listener;

use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\session\SessionHandler;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Sends notifications to users via nodePush
 */
class UserNotificationNodePushListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (
			$eventObj->getActionName() !== 'addRecipients'
			&& $eventObj->getActionName() !== 'createStackable'
			&& $eventObj->getActionName() !== 'createDefault'
		) {
			return;
		}

		$returnValues = $eventObj->getReturnValues();
		$notificationIDs = array_map(function ($item) {
			return $item['object']->notificationID;
		}, $returnValues['returnValues']);

		$userNotificationList = new \wcf\data\user\notification\UserNotificationList();
		$userNotificationList->sqlSelects .= "notification_event.eventID, object_type.objectType";
		$userNotificationList->sqlJoins = "
			LEFT JOIN	wcf".WCF_N."_user_notification_event notification_event
			ON		(notification_event.eventID = user_notification.eventID)
			LEFT JOIN	wcf".WCF_N."_object_type object_type
			ON		(object_type.objectTypeID = notification_event.objectTypeID)
		";
		$userNotificationList->sqlOrderBy = "user_notification.time DESC";
		$userNotificationList->setObjectIDs($notificationIDs);
		$userNotificationList->readObjects();
		$notifications = $userNotificationList->getObjects();

		UserRuntimeCache::getInstance()->cacheObjectIDs(\array_column($notifications, 'userID'));

		$realUser = WCF::getUser();
		foreach ($notifications as $notification) {
			try {
				SessionHandler::getInstance()->changeUser(
					UserRuntimeCache::getInstance()->getObject($notification->userID),
					true
				);

				$processedNotifications = UserNotificationHandler::getInstance()
					->processNotifications([$notification]);

				if ($processedNotifications['count'] == 0) {
					continue;
				}

				if ($processedNotifications['count'] != 1) {
					throw new \LogicException('Unreachable');
				}

				$processedNotification = $processedNotifications['notifications'][0];
				if ($processedNotification['notificationID'] != $notification->notificationID) {
					throw new \LogicException("Unreachable");
				}

				/** @var IUserNotificationEvent $event */
				$event = $processedNotification['event'];

				$notificationData = [
					'message' => $event->getMessage(),
					'author' => $event->getAuthor()->username,
					'link' => $event->getLink(),
				];

				\wcf\system\push\PushHandler::getInstance()->sendMessage([
					'message' => 'be.bastelstu.max.wcf.user.newNotification',
					'target' => [
						'users' => [ $notification->userID ]
					],
					'payload' => $notificationData
				]);
			}
			finally {
				\wcf\system\session\SessionHandler::getInstance()->changeUser($realUser, true);
			}
		}
	}
}
