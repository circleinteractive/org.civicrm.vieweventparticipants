<?php

require_once 'vieweventparticipants.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function vieweventparticipants_civicrm_config(&$config) {
  _vieweventparticipants_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function vieweventparticipants_civicrm_xmlMenu(&$files) {
  _vieweventparticipants_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function vieweventparticipants_civicrm_install() {
  _vieweventparticipants_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function vieweventparticipants_civicrm_uninstall() {
  _vieweventparticipants_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function vieweventparticipants_civicrm_enable() {
  _vieweventparticipants_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function vieweventparticipants_civicrm_disable() {
  _vieweventparticipants_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function vieweventparticipants_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _vieweventparticipants_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function vieweventparticipants_civicrm_managed(&$entities) {
  _vieweventparticipants_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function vieweventparticipants_civicrm_caseTypes(&$caseTypes) {
  _vieweventparticipants_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function vieweventparticipants_civicrm_angularModules(&$angularModules) {
  _vieweventparticipants_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function vieweventparticipants_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _vieweventparticipants_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_permission/
 */
function vieweventparticipants_civicrm_permission(&$permissions) {
  $permissions['view my event participants'] = array(
    ts('CiviEvent: view my event participants', array('domain' => 'org.civicrm.vieweventparticipants')),
    ts('Grants event creators permission to view their event\'s participants', array('domain' => 'org.civicrm.vieweventparticipants')),
  );

  $permissions['edit my event participants'] = array(
    ts('CiviEvent: edit my event participants', array('domain' => 'org.civicrm.vieweventparticipants')),
    ts('Grants event creators permission to edit their event\'s participants', array('domain' => 'org.civicrm.vieweventparticipants')),
  );
}

/**
 * Implements hook_civicrm_aclWhereClause().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_aclWhereClause/
 */
function vieweventparticipants_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  /*
   * Grant access for event creators to view or edit their events' participants.
   */
  if (!$contactID) {
    return;
  }

  if ($type != CRM_Core_Permission::VIEW && $type != CRM_Core_Permission::EDIT) {
    return;
  }

  /*
   * Only allow access if user also has our permission.
   */
  if ($type == CRM_Core_Permission::VIEW && !CRM_Core_Permission::check('view my event participants')) {
    return;
  }

  if ($type == CRM_Core_Permission::EDIT && !CRM_Core_Permission::check('edit my event participants')) {
    return;
  }

  if (!in_array('civicrm_participant', $whereTables)) {
    $tables['civicrm_participant'] = $whereTables['civicrm_participant']
      = "LEFT JOIN civicrm_participant ON contact_a.id = civicrm_participant.contact_id";
  }

  if (!in_array('civicrm_event', $whereTables)) {
    $tables['civicrm_event'] = $whereTables['civicrm_event']
      = "LEFT JOIN civicrm_event ON civicrm_participant.event_id = civicrm_event.id";
  }

  /*
   * If other ACLs are in place, e.g. through ACL UI, then we allow access to
   * the user's events' participants in addition to the contacts permitted by
   * these other ACLs. Hence OR.
   */
  if (!empty($where)) {
    $where = "($where) OR ";
  }

  $where .= sprintf("(civicrm_event.created_id = %d)", $contactID);
}
