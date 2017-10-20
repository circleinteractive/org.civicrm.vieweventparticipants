# Tests for org.civicrm.vieweventparticipants

**To run the tests:**

1. Set up a development build of CiviCRM, e.g. using [buildkit](https://docs.civicrm.org/dev/en/latest/tools/buildkit/).
2. Install the org.civicrm.vieweventparticipants extension in the default extensions directory, files/civicrm/ext/ .
3. In a terminal, go into the directory `files/civicrm/ext/org.civicrm.vieweventparticipants` .
4. Specify the location of your CiviCRM settings file, e.g.
```export CIVICRM_SETTINGS=/Users/myuser/buildkit/build/dmaster/sites/default/civicrm.settings.php```
using the actual location of your civicrm.settings.php file.
5. Run the tests with:
```phpunit4 ./tests/phpunit/CRM/Vieweventparticipants/ACLTest.php```
