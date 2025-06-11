import './bootstrap';

import * as PusherPushNotifications from "@pusher/push-notifications-web"

const beamsClient = new PusherPushNotifications.Client({
    instanceId: 'e6c6a85a-9f57-44ec-8f3d-b9da25165d4f',
});

beamsClient.start()
    .then(() => beamsClient.addDeviceInterest('hello'))
    .then(() => console.log('Successfully registered and subscribed!'))
    .catch(console.error);
