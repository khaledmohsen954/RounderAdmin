# Admin notifications

The top-bar bell shows permission-filtered activity, unread counts and the latest 20 events. It refreshes every 30 seconds while the tab is visible. This is an in-dashboard feed, not mobile push or WebSockets.

## Events
- Emergency request created (urgent): emergencies.view.
- Property created or resident linked to property: realestate.view.
- Service booking created: orders.view.
- Provider profile created: providers.view.

Opening an event marks it read and opens its dashboard section. Each item includes the source record ID. A separate mark-as-read control and mark-all-read are available.

## Persistence and authorization
The Notifications module owns admin_alerts and admin_alert_reads. Read receipts reference admins and alerts with foreign keys and a unique pair; reading never marks another administrator's notification read. Permissions are checked at every request, including old notifications. Unknown or inaccessible notification IDs return 404. POST operations use the web CSRF middleware.

Creation observers delegate to AdminAlertService. Events are stored inside the originating transaction so rollback removes both business changes and their alerts. Resident pivot attachment explicitly records its alert because pivot attach does not fire a normal model creation observer. This is intentionally transactional persistence rather than an after-commit external side effect.

No old production events are backfilled. Deploy the new migration before serving the updated application. Business records retain their own module ownership; the alert's type and source ID identify the record without copying personal details. Deleting the source leaves its audit notification and a safe section link.

## Verification
AdminNotificationsTest covers permission filtering, read-state isolation, revocation, repeated reads, transaction rollback and creation observation. The browser smoke test opens the bell and checks feed loading.
