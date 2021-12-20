# View My Event Participants

## Overview

View My Event Participants (org.civicrm.vieweventparticipants) is a CiviCRM extension granting access for event creators to view or edit their events' participants.

## Why would you need this extension?

This extension is only needed if you have users who create events but who do not have permission to view or edit all the participant contacts of those events. I.e. the event creators do not have "view all contacts" or "edit all contacts" permission and do not have permission via ACLs (access control lists) to view or edit all the relevant contacts.

## What does this extension do?

It implements two new permissions: 'view my event participants' and 'edit my event participants'. These grant the user access to all the contacts who are participants of events that were created by the user.

**N.B.** If other ACLs are in place, e.g. through CiviCRM's "Manage ACLs" user interface, then this extension allows access to the user's events' participants _in addition to_ the contacts permitted by these other ACLs. Consider whether this is what you want: in some use cases, you might instead want to grant access to view/edit only the participants who are also permitted by the other ACLs.

E.g. suppose there's an existing ACL that gives a user access to all contacts with a UK address. With this extension, that user would have access to all UK contacts _plus_ all participants of events created by the user - even if those participants are outside the UK.

**Note:** access is granted by means of a hook. Consequently this will not show up in the CiviCRM user interface under Manage ACLs: it's done "under the hood".

## How do I use it?

1. Install the extension.
Currently this extension is not available for automated distribution through CiviCRM's Extension management screen and so must be manually downloaded onto the server, from https://github.com/circleinteractive/org.civicrm.vieweventparticipants and unpacked into your CiviCRM extensions directory. See https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/ .
2. Enable the extension.
From the CiviCRM menu, go to Administer -> System settings -> Extensions. Click the **Enable** link for View Event Participants (org.civicrm.vieweventparticipants).
3. Grant permissions.
In your CMS's permissions management screen, grant the 'view my event participants' or 'edit my event participants' permissions to the users/roles who need to be able to view/edit their event participants.

## Other extensions
To reassign an event to a different creator, install the [Edit Event Manager](https://lab.civicrm.org/extensions/editeventmanager) extension.

If you need to grant permission to view contacts based on a participant's event role (e.g. all instructors can see attendee contacts), use the [Event Permissions by Role](https://lab.civicrm.org/extensions/eventpermissionsbyrole) extension.
