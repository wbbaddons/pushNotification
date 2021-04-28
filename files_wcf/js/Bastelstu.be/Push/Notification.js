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

define([ 'Bastelstu.be/_Push'
       , 'WoltLabSuite/Core/Language'
       , 'WoltLabSuite/Core/Notification/Handler'
       , 'WoltLabSuite/Core/Ui/Notification'
       ], function (Push, Language, NotificationHandler, UiNotification) {
	"use strict";

	class Notification {
		constructor() {
			Push
			.onMessage('be.bastelstu.max.wcf.user.newNotification', this.notify.bind(this))
			.catch(error => { console.debug(error) })
		}
		
		notify(payload) {
			UiNotification.show(Language.get('wcf.user.notification.new'), 'info')

			NotificationHandler._dispatchRequest()
		}
	}

	return Notification
});
