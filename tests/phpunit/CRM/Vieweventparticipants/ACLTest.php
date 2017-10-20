<?php

use CRM_Vieweventparticipants_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Unit test suite for View My Event Participants extension (org.civicrm.vieweventparticipants).
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Vieweventparticipants_ACLTest extends CRM_Vieweventparticipants_BaseTestClass {

  // A realistic set of event permissions EXCLUDING view/edit all contacts & our permissions.
  private $basicPermissions = array('access CiviCRM', 'add contacts', 'view my contact', 'edit my contact', 'access CiviEvent', 'view event info', 'register for events');

  // Id of test event created by the logged-in user.
  private $userEventId;

  // Id of test event created by another user.
  private $otherEventId;

  // Ids of test contacts.
  private $contactIds;

  /**
   * Test that no participants are made accessible when no permissions granted.
   */
  public function testViewAllOrNone() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Give the user 'view all contacts' permission, just to check can see all contacts.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array('view all contacts');
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    sort($allowedContacts);
    $this->assertEquals($allowedContacts, $this->contactIds, "All contacts should be viewable when 'view all contacts'.");

    // Give the user no permissions.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array();

    // Check which contacts they have access to now: should be none.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertEmpty($allowedContacts, "No contacts should be viewable when no 'view my event participants'.");
  }

  /**
   * Test that no participants are made accessible without the permissions defined by this extension.
   */
  public function testBasicPermissions() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Give the user basic permissions only (EXCLUDING view/edit all contacts & our permissions).
    CRM_Core_Config::singleton()->userPermissionClass->permissions = $this->basicPermissions;

    // Check which contacts they have access to VIEW now: should be user only, as no participants created.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be viewable via 'view my contact'.");
  }

  /**
   * Test that no contacts are accessible when there are no participants.
   */
  public function testNoParticipants() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Give the user basic permissions + our 'view my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('view my event participants'));

    // Check which contacts they have access to VIEW now: should be user only, as no participants created.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be viewable, no participants.");

    // Check which contacts they have access to EDIT now: should be user only, as no participants created.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be editable, no participants.");

    // Give the user basic permissions + our 'edit my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('edit my event participants'));

    // Check which contacts they have access to VIEW now: should be user only, as no participants created.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be viewable, no participants.");

    // Check which contacts they have access to EDIT now: should be user only, as no participants created.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be editable, no participants.");
  }

  /**
   * Test that only user's events' participants can be viewed by ACL'd user.
   */
  public function testViewUserEventParticipants() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Give the user basic permissions + our 'view my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('view my event participants'));

    // Check which contacts they have access to VIEW now:
    // should be the user + participants of the user's event: contacts 0, 2 & 4.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertNotContains($this->contactIds[1], $allowedContacts, "User should NOT have an ACL permission to view contact 1.");
    $this->assertContains($this->contactIds[2], $allowedContacts, "User should have an ACL permission to view contact 2 (participant of user's event)");
    $this->assertNotContains($this->contactIds[3], $allowedContacts, "User should NOT have an ACL permission to view contact 3 (participant of other event)");
    $this->assertContains($this->contactIds[4], $allowedContacts, "User should have an ACL permission to view contact 4 (participant of user's event)");
    $this->assertNotContains($this->contactIds[5], $allowedContacts, "User should NOT have an ACL permission to view contact 5 (participant of other event)");

    // Check which contacts they have access to EDIT now: should be user only, as no edit ACL.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "only own contact should be editable, no participants.");
  }

  /**
   * Test that only user's events' participants can be edited by ACL'd user.
   */
  public function testEditUserEventParticipants() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Give the user basic permissions + our 'edit my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('edit my event participants'));

    // Check which contacts they have access to EDIT now:
    // should be the user + participants of the user's event: contacts 0, 2 & 4.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertNotContains($this->contactIds[1], $allowedContacts, "User should NOT have an ACL permission to edit contact 1.");
    $this->assertContains($this->contactIds[2], $allowedContacts, "User should have an ACL permission to edit contact 2 (participant of user's event)");
    $this->assertNotContains($this->contactIds[3], $allowedContacts, "User should NOT have an ACL permission to edit contact 3 (participant of other event)");
    $this->assertContains($this->contactIds[4], $allowedContacts, "User should have an ACL permission to edit contact 4 (participant of user's event)");
    $this->assertNotContains($this->contactIds[5], $allowedContacts, "User should NOT have an ACL permission to edit contact 5 (participant of other event)");
  }

  /**
   * Test with group ACL only, check that no participants are made accessible
   * without the permissions defined by this extension.
   */
  public function testACLGroupOnly() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Create group ACL: user has permission to view members of ACL'd group = contact 1.
    $this->createGroupACL();

    // Give the user basic permissions only (EXCLUDING view/edit all contacts & our permissions).
    CRM_Core_Config::singleton()->userPermissionClass->permissions = $this->basicPermissions;

    // Check which contacts they have access to VIEW now: should be user + ACL'd group = contacts 0 & 1.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertContains($this->contactIds[1], $allowedContacts, "User should have permission via group ACL to view contact 1.");
    $this->assertCount(2, $allowedContacts, "Only contacts 0 & 1 should be viewable via 'view my contact' + group ACL.");

    // Check which contacts they have access to EDIT now: should be user only = contacts 0.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "Only contact 0 should be editable via 'edit my contact'.");
  }

  /**
   * Test with group ACL + 'view my event participants',
   * check that participants of user's event are made accessible.
   */
  public function testACLGroupPlusViewUserEventParticipants() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Create group ACL: user has permission to view members of ACL'd group = contact 1.
    $this->createGroupACL();

    // Give the user basic permissions + our 'view my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('view my event participants'));

    // Check which contacts they have access to VIEW now:
    // should be user + ACL'd group + the participants of the user's event: contacts 0, 1, 2 & 4.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::VIEW);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'view my contact' to view contact 0 (user's own contact)");
    $this->assertContains($this->contactIds[1], $allowedContacts, "User should have an ACL permission to view contact 1 (via group ACL).");
    $this->assertContains($this->contactIds[2], $allowedContacts, "User should have an ACL permission to view contact 2 (participant of user's event).");
    $this->assertNotContains($this->contactIds[3], $allowedContacts, "User should NOT have an ACL permission to view contact 3 (participant of other event)");
    $this->assertContains($this->contactIds[4], $allowedContacts, "User should have an ACL permission to view contact 4 (participant of user's event).");
    $this->assertNotContains($this->contactIds[5], $allowedContacts, "User should NOT have an ACL permission to view contact 5 (participant of other event)");

    // Check which contacts they have access to EDIT now: should be user only = contact 0.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertCount(1, $allowedContacts, "Only contact 0 should be editable via 'edit my contact'.");
  }

  /**
   * Test with group ACL + 'edit my event participants',
   * check that participants of user's event are made accessible.
   */
  public function testACLGroupPlusEditUserEventParticipants() {
    // Create contacts and events.
    $this->createContactsAndEvents();

    // Create event participants: contacts 2 & 4 registered for user's event.
    $this->createParticipants();

    // Create group ACL: user has permission to view members of ACL'd group = contact 1.
    $this->createGroupACL();

    // Give the user basic permissions + our 'edit my event participants' permission.
    CRM_Core_Config::singleton()->userPermissionClass->permissions = array_merge($this->basicPermissions, array('edit my event participants'));

    // Check which contacts they have access to EDIT now:
    // should be user + the participants of the user's event: contacts 0, 2 & 4.
    $allowedContacts = CRM_Contact_BAO_Contact_Permission::allowList($this->contactIds, CRM_Core_Permission::EDIT);
    $this->assertContains($this->contactIds[0], $allowedContacts, "User should have permission via 'edit my contact' to edit contact 0 (user's own contact)");
    $this->assertNotContains($this->contactIds[1], $allowedContacts, "User should NOT have an ACL permission to edit contact 1 (view only via group ACL).");
    $this->assertContains($this->contactIds[2], $allowedContacts, "User should have an ACL permission to edit contact 2 (participant of user's event).");
    $this->assertNotContains($this->contactIds[3], $allowedContacts, "User should NOT have an ACL permission to edit contact 3 (participant of other event)");
    $this->assertContains($this->contactIds[4], $allowedContacts, "User should have an ACL permission to edit contact 4 (participant of user's event).");
    $this->assertNotContains($this->contactIds[5], $allowedContacts, "User should NOT have an ACL permission to edit contact 5 (participant of other event)");
  }

  /*********************
   * Scenario builders *
   *********************/

  /**
   * Scenario builder: create contacts & events for testing participant ACL.
   */
  protected function createContactsAndEvents() {
    // Get logged in user.
    $user_id = $this->createLoggedInUser();
    $this->assertNotEmpty($user_id);

    // Create test contacts.
    $ada_id    = $this->individualCreate(array('first_name' => 'Ada', 'last_name' => 'Ant'));
    $bert_id    = $this->individualCreate(array('first_name' => 'Bert', 'last_name' => 'Bee'));
    $celia_id = $this->individualCreate(array('first_name' => 'Celia', 'last_name' => 'Caterpillar'));
    $derek_id = $this->individualCreate(array('first_name' => 'Derek', 'last_name' => 'Dung-Beetle'));
    $elsie_id = $this->individualCreate(array('first_name' => 'Elsie', 'last_name' => 'Earwig'));

    $this->contactIds = array($user_id, $ada_id, $bert_id, $celia_id, $derek_id, $elsie_id);

    // Create event, created by current user.
    $eventParams['created_id'] = $user_id;
    $event = $this->eventCreate($eventParams);
    $this->userEventId = $event['id'];
    $this->assertEquals($event['values'][$event['id']]['created_id'], $user_id, "First event's creator should be the user.");

    // Create event, created by other user.
    $eventParams['created_id'] = $ada_id;
    $event = $this->eventCreate($eventParams);
    $this->otherEventId = $event['id'];
    $this->assertNotEquals($event['values'][$event['id']]['created_id'], $user_id, "Second event's creator should NOT be the user.");
  }

  /**
   * Scenario builder: create participants for testing participant ACL.
   */
  protected function createParticipants() {
    // Create participants of user's event: contacts 2 & 4.
    $participantParams['event_id'] = $this->userEventId;

    $participantParams['contact_id'] = $this->contactIds[2];
    $participant = $this->participantCreate($participantParams);

    $participantParams['contact_id'] = $this->contactIds[4];
    $participant = $this->participantCreate($participantParams);

    // Create participants of other event: contacts 3 & 5.
    $participantParams['event_id'] = $this->otherEventId;

    $participantParams['contact_id'] = $this->contactIds[3];
    $participant = $this->participantCreate($participantParams);

    $participantParams['contact_id'] = $this->contactIds[5];
    $participant = $this->participantCreate($participantParams);
  }

  /**
   * Scenario builder: create group ACL. Based on CiviUnitTestCase::setupACL().
   */
  protected function createGroupACL() {
    // Create ACL group.
    $aclGroup = $this->callAPISuccess('Group', 'create', array(
      'title' => 'Test ACL group',
      'name' => 'Test_ACL_group',
      'is_active' => 1,
      'group_type' => array('1' => 1), // 1 = ACL group
    ));

    // Add logged in user to ACL group.
    $this->callAPISuccess('group_contact', 'create', array(
      'group_id' => $aclGroup['id'],
      'contact_id' => CRM_Core_Session::singleton()->getLoggedInContactID(),
    ));

    // Create group to be ACL'd.
    $targetGroup = $this->callAPISuccess('Group', 'create', array(
      'title' => 'Test target group',
      'name' => 'Test_target_group',
      'is_active' => 1,
    ));

    // Add contact 1 (Ada Ant) to target group.
    $this->callAPISuccess('group_contact', 'create', array(
      'group_id' => $targetGroup['id'],
      'contact_id' => $this->contactIds[1],
    ));

    // Create ACL role.
    $optionGroupID = $this->callAPISuccessGetValue('option_group', array('return' => 'id', 'name' => 'acl_role'));

    $aclRoleValue = 1 + CRM_Core_DAO::singleValueQuery("
      SELECT MAX(CAST(value AS UNSIGNED))
      FROM civicrm_option_value
      WHERE option_group_id = %1
    ", array(1 => array($optionGroupID, 'Integer')));

    $optionValue = $this->callAPISuccess('option_value', 'create', array(
      'option_group_id' => $optionGroupID,
      'label' => 'Test ACL role',
      'value' => $aclRoleValue,
    ));

    CRM_Core_DAO::executeQuery("
      DELETE FROM civicrm_acl_cache
    ");

    CRM_Core_DAO::executeQuery("
      DELETE FROM civicrm_acl_contact_cache
    ");

    // Assign Test ACL role to Test ACL group. (Tried using API but didn't work.)
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_acl_entity_role (`acl_role_id`, `entity_table`, `entity_id`, `is_active`)
      VALUES (%1, 'civicrm_group', {$aclGroup['id']}, 1);
    ", array(1 => array($aclRoleValue, 'Integer')));

    // Create ACL for Test ACL role to view Test target group.
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_acl (`name`, `entity_table`, `entity_id`, `operation`, `object_table`, `object_id`, `is_active`)
      VALUES ('View target group', 'civicrm_acl_role', %1, 'View', 'civicrm_saved_search', {$targetGroup['id']}, 1
      );
    ", array(1 => array($aclRoleValue, 'Integer')));
  }

}
