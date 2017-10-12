# org.civicrm.vieweventparticipants
CiviCRM extension granting access for event creators to view their events' participants.

##Why would you need this extension?

This extension is only needed if you have users who create events but who do not have permission to view all the participant contacts of those events. I.e. the event creators do not have "view all contacts" permission and do not have permission via ACLs (access control lists) to view all the relevant contacts.

##What does this extension do?

It implements a new permission, 'view my event participants'. This grants the user access to all the contacts who are participants of events that were created by the user.

**N.B.** If other ACLs are in place, e.g. through CiviCRM's "Manage ACLs" user interface, then this extension allows access to the user's events' participants _in addition to_ the contacts permitted by these other ACLs. Consider whether this is what you want: in some use cases, you might instead want to grant access to view only the participants who are also permitted by the other ACLs.

E.g. suppose there's an existing ACL that gives a user access to all contacts with a UK address. With this extension, that user would have access to all UK contacts _plus_ all participants of events created by the user - even if those participants are outside the UK.

**Note:** access is granted by means of a hook. Consequently this will not show up in the CiviCRM user interface under Manage ACLs: it's done "under the hood".

##How do I use it?

1. Install the extension.
Currently this extension is not available for automated distribution through CiviCRM's Extension management screen and so must be manually downloaded onto the server, from https://github.com/davejenx/org.civicrm.vieweventparticipants .
2. Enable the extension.
From the CiviCRM menu, go to Administer -> System settings -> Extensions. Click the **Enable** link for View Event Participants (org.civicrm.vieweventparticipants).
3. Grant permissions.
In your CMS's permissions management screen, grant the 'view my event participants' permission to the users/roles who need to be able to view their event participants.
