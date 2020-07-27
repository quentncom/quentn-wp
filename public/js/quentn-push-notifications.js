document.addEventListener('DOMContentLoaded', () => {

    const applicationServerPublicKey = 'BIH-nytnvEd6j4eA6WqgH7yDaVSNPN1H0JgyP95Xn3yDJqPyMky51c7rp6uYYjvBFbE0tpOybzjHD1TjGX6kygE';

    const pushButton = document.querySelector('.js-push-btn');

    let isSubscribed = false;
    let swRegistration = null;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        console.log('Service Worker and Push are supported');

        navigator.serviceWorker.register(wp_qntn_url.plugin_dir_url+'js/quentn-service-worker.js')
            .then(function(swReg) {
                console.log('Service Worker is registered', swReg);
                swRegistration = swReg;
                initializeUI();
            })
            .catch(function(error) {
                console.error('Service Worker Error', error);
            });
    } else {
        console.warn('Push messaging is not supported');
        pushButton.textContent = 'Push Not Supported';
    }

    function urlB64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }


    function initializeUI() {
        pushButton.addEventListener('click', function() {
            pushButton.disabled = true;
            if (isSubscribed) {
                unsubscribeUser();
            } else {
                subscribeUser();
            }
        });

        // Set the initial subscription value
        swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                isSubscribed = !(subscription === null);

                if (isSubscribed) {
                    updateSubscriptionOnPage(subscription);
                    console.log('User IS subscribed.');
                } else {
                    console.log('User is NOT subscribed.');
                }
                updateBtn();
            });
    }


    function updateBtn() {
        if (Notification.permission === 'denied') {
            pushButton.textContent = 'Push Messaging Blocked';
            pushButton.disabled = true;
            updateSubscriptionOnServer(null);
            return;
        }

        if (isSubscribed) {
            pushButton.textContent = 'Disable Push Messaging';
        } else {
            pushButton.textContent = 'Enable Push Messaging';
        }

        pushButton.disabled = false;
    }

    function subscribeUser() {
        const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
        swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
            .then(function(subscription) {
                console.log('User is subscribed.');
                updateSubscriptionOnServer(subscription);

                isSubscribed = true;

                updateBtn();
            })
            .catch(function(error) {
                console.error('Failed to subscribe the user: ', error);
                updateBtn();
            });
    }

    //delete supscription from server
    function deleteSubscription(subscription)
    {
        jQuery.ajax( {
            type: 'post',
            url: wp_qntn_url.ajaxurl,
            data: {
                security: wp_qntn_url.delete_notify_security,
                action: 'delete_push_notification_subscription',
                endpoint: subscription.endpoint,
            },
            success: function (result) {
                console.log("subscription deleted from server");
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.status);
                console.log(thrownError);
            }
        } );
    }


    function updateSubscriptionOnPage(subscription) {

        var qntn = (new URL(window.location.href)).searchParams.get("qntn");
        var qntnTrck = getCookie("qntnTrck");

        if ( ! qntn && qntnTrck === null) {
            return;
        }

        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        jQuery.ajax( {
            type: 'post',
            url: wp_qntn_url.ajaxurl,
            data: {
                security: wp_qntn_url.update_notify_security,
                action: 'update_push_notification_subscription',
                settings: JSON.stringify(subscription),
                endpoint: subscription.endpoint,
                p256dh: subscription.toJSON().keys.p256dh,
                auth: subscription.toJSON().keys.auth,
                qntn: qntn
            },
            success: function ( response ){
                console.log('update success: '+ response)
            },
            error: function ( response ){
                console.log('update Failure: ' + response)
            },
        } );



    }

    function updateSubscriptionOnServer(subscription, method = 'POST') {

        const subscriptionJson = document.querySelector('.js-subscription-json');
        const subscriptionDetails =
            document.querySelector('.js-subscription-details');

        if (subscription) {
            subscriptionJson.textContent = JSON.stringify(subscription);
            subscriptionDetails.classList.remove('is-invisible');
        } else {
            subscriptionDetails.classList.add('is-invisible');
        }


        if (subscription) {

            var qntn = ( new URL( window.location.href ) ).searchParams.get( "qntn" );
            const key = subscription.getKey('p256dh');
            const token = subscription.getKey('auth');
            const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

            jQuery.ajax( {
                type: 'post',
                url: wp_qntn_url.ajaxurl,
                data: {
                    security: wp_qntn_url.create_notify_security,
                    action: 'create_push_notification_subscription',
                    settings: JSON.stringify(subscription),
                    endpoint: subscription.endpoint,
                    p256dh: subscription.toJSON().keys.p256dh,
                    auth: subscription.toJSON().keys.auth,
                    qntn: qntn,
                },
                success: function ( response ){
                    console.log('create success: '+ response)
                },
                error: function ( response ){
                    console.log('create Failure: ' + response)
                },
            } );
        }

    }

    function unsubscribeUser() {
        swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                if (subscription) {
                    // TODO: Tell application server to delete subscription
                    deleteSubscription(subscription);
                    return subscription.unsubscribe();
                }
            })
            .catch(function(error) {
                console.log('Error unsubscribing', error);
            })
            .then(function() {
                updateSubscriptionOnServer(null);

                console.log('User is unsubscribed.');
                isSubscribed = false;

                updateBtn();
            });
    }

    function getCookie(name) {
        // Split cookie string and get all individual name=value pairs in an array
        var cookieArr = document.cookie.split(";");

        // Loop through the array elements
        for(var i = 0; i < cookieArr.length; i++) {
            var cookiePair = cookieArr[i].split("=");

            /* Removing whitespace at the beginning of the cookie name
            and compare it with the given string */
            if(name == cookiePair[0].trim()) {
                // Decode the cookie value and return
                return decodeURIComponent(cookiePair[1]);
            }
        }

        // Return null if not found
        return null;
    }
});
