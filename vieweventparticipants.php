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
 * @param array $files
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
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
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
 * @param array $caseTypes
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
 * Implements hook_civicrm_aclWhereClause().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_aclWhereClause
 */
function vieweventparticipants_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  /*
   * Grant access for event creators to view their events' participants.
   */
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": start: \$contactID '$contactID', \$where '$where'.");
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": start: \$tables " . print_r($tables, TRUE));
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": start: \$whereTables " . print_r($whereTables, TRUE));
  if (!$contactID) {
    return;
  }

  // TODO Only allow access if user also has our permission.
  // TODO Optimisation: only add clause if user has created an event.

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
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": end: \$where '$where'.");
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": end: \$tables " . print_r($tables, TRUE));
  CRM_Core_Error::debug_log_message(__FUNCTION__ . ": end: \$whereTables " . print_r($whereTables, TRUE));
}
