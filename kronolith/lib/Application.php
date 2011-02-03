<?php
/**
 * Kronolith application API.
 *
 * This file defines Horde's core API interface. Other core Horde libraries
 * can interact with Kronolith through this API.
 *
 * Copyright 2010-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @package Kronolith
 */

/* Determine the base directories. */
if (!defined('KRONOLITH_BASE')) {
    define('KRONOLITH_BASE', dirname(__FILE__) . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If Horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(KRONOLITH_BASE . '/config/horde.local.php')) {
        include KRONOLITH_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', KRONOLITH_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Kronolith_Application extends Horde_Registry_Application
{
    /**
     * Does this application support an ajax view?
     *
     * @var boolean
     */
    public $ajaxView = true;

    /**
     * Does this application support a mobile view?
     *
     * @var boolean
     */
    public $mobileView = true;

    /**
     * The application's version.
     *
     * @var string
     */
    public $version = 'H4 (3.0-git)';

    /**
     * Initialization function.
     *
     * Global variables defined:
     * - $kronolith_shares: TODO
     * - $linkTags: <link> tags for common-header.inc.
     */
    protected function _init()
    {
        /* For now, autoloading the Content_* classes depend on there being a
         * registry entry for the 'content' application that contains at least
         * the fileroot entry. */
        $GLOBALS['injector']->getInstance('Horde_Autoloader')->addClassPathMapper(new Horde_Autoloader_ClassPathMapper_Prefix('/^Content_/', $GLOBALS['registry']->get('fileroot', 'content') . '/lib/'));
        if (!class_exists('Content_Tagger')) {
            throw new Horde_Exception('The Content_Tagger class could not be found. Make sure the registry entry for the Content system is present.');
        }

        $GLOBALS['injector']->bindFactory('Kronolith_Geo', 'Kronolith_Factory_Geo', 'create');

        /* Set the timezone variable, if available. */
        $GLOBALS['registry']->setTimeZone();

        /* Create a share instance. */
        $GLOBALS['kronolith_shares'] = $GLOBALS['injector']->getInstance('Horde_Core_Factory_Share')->create();

        Kronolith::initialize();

        $GLOBALS['linkTags'] = array();
        foreach ($GLOBALS['display_calendars'] as $calendar) {
            $GLOBALS['linkTags'][] = '<link href="' . Kronolith::feedUrl($calendar) . '" rel="alternate" type="application/atom+xml" />';
        }
    }

    /**
     * Returns a list of available permissions.
     *
     * @return array  An array describing all available permissions.
     */
    public function perms()
    {
        return array(
            'max_events' => array(
                'title' => _("Maximum Number of Events"),
                'type' => 'int'
            )
        );
    }

    /**
     * Add additional items to the menu.
     *
     * @param Horde_Menu $menu  The menu object.
     */
    public function menu($menu)
    {
        global $browser, $conf, $injector, $notification, $prefs, $registry;

        /* Check here for guest calendars so that we don't get multiple
         * messages after redirects, etc. */
        if (!$registry->getAuth() && !count(Kronolith::listCalendars())) {
            $notification->push(_("No calendars are available to guests."));
        }

        $menu->add(Horde::url($prefs->getValue('defaultview') . '.php'), _("_Today"), 'today.png', null, null, null, '__noselection');
        if (Kronolith::getDefaultCalendar(Horde_Perms::EDIT) &&
            ($injector->getInstance('Horde_Perms')->hasAppPermission('max_events') === true ||
             $injector->getInstance('Horde_Perms')->hasAppPermission('max_events') > self::countEvents())) {
            $menu->add(Horde::url('new.php')->add('url', Horde::selfUrl(true, false, true)), _("_New Event"), 'new.png');
        }

        if ($browser->hasFeature('dom')) {
            Horde_Core_Ui_JsCalendar::init(array(
                'click_month' => true,
                'click_week' => true,
                'click_year' => true,
                'full_weekdays' => true
            ));
            Horde::addScriptFile('goto.js', 'kronolith');
            Horde::addInlineJsVars(array(
                'KronolithGoto.dayurl' => strval(Horde::url('day.php')),
                'KronolithGoto.monthurl' => strval(Horde::url('month.php')),
                'KronolithGoto.weekurl' => strval(Horde::url('week.php')),
                'KronolithGoto.yearurl' => strval(Horde::url('year.php'))
            ));
            $menu->add(new Horde_Url(''), _("_Goto"), 'goto.png', null, '', null, 'kgotomenu');
        }
        $menu->add(Horde::url('search.php'), _("_Search"), 'search.png');

        /* Import/Export. */
        if ($conf['menu']['import_export']) {
            $menu->add(Horde::url('data.php'), _("_Import/Export"), 'data.png');
        }
    }

    /**
     * Returns the specified permission for the given app permission.
     *
     * @param string $permission  The permission to check.
     * @param mixed $allowed      The allowed permissions.
     * @param array $opts         Additional options (NONE).
     *
     * @return mixed  The value of the specified permission.
     */
    public function hasPermission($permission, $allowed, $opts = array())
    {
        switch ($permission) {
        case 'max_events':
            $allowed = max($allowed);
            break;
        }

        return $allowed;
    }

    /**
     * Code to run on init when viewing prefs for this application.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     */
    public function prefsInit($ui)
    {
        global $prefs, $registry;

        /* Suppress prefGroups display. */
        if (!$registry->hasMethod('contacts/sources')) {
            $ui->suppressGroups[] = 'addressbooks';
        }

        if ($prefs->isLocked('default_alarm')) {
            $ui->suppressGroups[] = 'event_options';
        }
    }

    /**
     * Determine active prefs when displaying a group.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     */
    public function prefsGroup($ui)
    {
        global $conf, $prefs;

        foreach ($ui->getChangeablePrefs() as $val) {
            switch ($val) {
            case 'day_hour_end':
            case 'day_hour_start':
                $hour = array();
                for ($i = 0; $i <= 48; ++$i) {
                    $hour[$i] = date(($prefs->getValue('twentyFour')) ? 'G:i' : 'g:ia', mktime(0, $i * 30, 0));
                }
                $ui->override[$val] = $hour;
                break;

            case 'default_share':
                foreach (Kronolith::listInternalCalendars(false, Horde_Perms::EDIT) as $id => $calendar) {
                    $ui->override['default_share'][$id] = $calendar->get('name');
                }
                break;

            case 'event_alarms_select':
                if (empty($conf['alarms']['driver']) ||
                    $prefs->isLocked('event_alarms_select')) {
                    $ui->suppress[] = 'event_alarms';
                } else {
                    Horde_Core_Prefs_Ui_Widgets::alarmInit();
                }
                break;

            case 'fb_cals':
                $fb_list = array();
                foreach (Kronolith::listCalendars() as $fb_cal => $cal) {
                    if ($cal->display()) {
                        $fb_list[htmlspecialchars($fb_cal)] = htmlspecialchars($cal->name());
                    }
                }
                $ui->override['fb_cals'] = $fb_list;
                break;

            case 'sourceselect':
                Horde_Core_Prefs_Ui_Widgets::addressbooksInit();
                break;
            }
        }
    }

    /**
     * Generate code used to display a special preference.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     * @param string $item             The preference name.
     *
     * @return string  The HTML code to display on the prefs page.
     */
    public function prefsSpecial($ui, $item)
    {
        switch ($item) {
        case 'default_alarm_management':
            return $this->_defaultAlarmManagement($ui);

        case 'event_alarms_select':
            return Horde_Core_Prefs_Ui_Widgets::alarm(array(
                'label' => _("Choose how you want to receive reminders for events with alarms:"),
                'pref' => 'event_alarms'
            ));

        case 'sourceselect':
            $search = Kronolith::getAddressbookSearchParams();
            return Horde_Core_Prefs_Ui_Widgets::addressbooks(array(
                'fields' => $search['fields'],
                'sources' => $search['sources']
            ));
        }

        return '';
    }

    /**
     * Special preferences handling on update.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     * @param string $item             The preference name.
     *
     * @return boolean  True if preference was updated.
     */
    public function prefsSpecialUpdate($ui, $item)
    {
        switch ($item) {
        case 'default_alarm_management':
            $GLOBALS['prefs']->setValue('default_alarm', (int)$ui->vars->alarm_value * (int)$ui->vars->alarm_unit);
            return true;

        case 'event_alarms_select':
            $data = Horde_Core_Prefs_Ui_Widgets::alarmUpdate($ui, array('pref' => 'event_alarms'));
            if (!is_null($data)) {
                $GLOBALS['prefs']->setValue('event_alarms', serialize($data));
                return true;
            }
            break;

        case 'remote_cal_management':
            return $this->_prefsRemoteCalManagement($ui);

        case 'sourceselect':
            return $this->_prefsSourceselect($ui);
        }

        return false;
    }

    /**
     * Called when preferences are changed.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     */
    public function prefsCallback($ui)
    {
        if ($GLOBALS['prefs']->isDirty('event_alarms')) {
            try {
                $alarms = $GLOBALS['registry']->callByPackage('kronolith', 'listAlarms', array($_SERVER['REQUEST_TIME']));
                if (!empty($alarms)) {
                    $horde_alarm = $GLOBALS['injector']->getInstance('Horde_Alarm');
                    foreach ($alarms as $alarm) {
                        $alarm['start'] = new Horde_Date($alarm['start']);
                        $alarm['end'] = new Horde_Date($alarm['end']);
                        $horde_alarm->set($alarm);
                    }
                }
            } catch (Exception $e) {}
        }
    }

    /**
     * Create code for default alarm management.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     *
     * @return string  HTML UI code.
     */
    protected function _defaultAlarmManagement($ui)
    {
        $t = $GLOBALS['injector']->createInstance('Horde_Template');
        $t->setOption('gettext', true);

        if ($alarm_value = $GLOBALS['prefs']->getValue('default_alarm')) {
            if ($alarm_value % 10080 == 0) {
                $alarm_value /= 10080;
                $t->set('week', true);
            } elseif ($alarm_value % 1440 == 0) {
                $alarm_value /= 1440;
                $t->set('day', true);
            } elseif ($alarm_value % 60 == 0) {
                $alarm_value /= 60;
                $t->set('hour', true);
            } else {
                $t->set('minute', true);
            }
        } else {
            $t->set('minute', true);
        }

        $t->set('alarm_value', intval($alarm_value));

        return $t->fetch(KRONOLITH_TEMPLATES . '/prefs/defaultalarm.html');
    }

    /**
     * Create code for remote calendar management.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     *
     * @return string  HTML UI code.
     */
    protected function _prefsRemoteCalManagement($ui)
    {
        $calName = $ui->vars->remote_name;
        $calUrl  = trim($ui->vars->remote_url);
        $calUser = trim($ui->vars->remote_user);
        $calPasswd = trim($ui->vars->remote_password);

        $key = $GLOBALS['registry']->getAuthCredential('password');
        if ($key) {
            $calUser = base64_encode(Secret::write($key, $calUser));
            $calPasswd = base64_encode(Secret::write($key, $calPasswd));
        }

        $calActionID = isset($ui->vars->remote_action)
            ? $ui->vars->remote_action
            : 'add';

        if ($calActionID == 'add') {
            if (!empty($calName) && !empty($calUrl)) {
                $cals = unserialize($GLOBALS['prefs']->getValue('remote_cals'));
                $cals[] = array('name' => $calName,
                    'url'  => $calUrl,
                    'user' => $calUser,
                    'password' => $calPasswd);
                $GLOBALS['prefs']->setValue('remote_cals', serialize($cals));
            }
        } elseif ($calActionID == 'delete') {
            $cals = unserialize($GLOBALS['prefs']->getValue('remote_cals'));
            foreach ($cals as $key => $cal) {
                if ($cal['url'] == $calUrl) {
                    unset($cals[$key]);
                    break;
                }
            }
            $GLOBALS['prefs']->setValue('remote_cals', serialize($cals));
        } elseif ($calActionID == 'edit') {
            $cals = unserialize($GLOBALS['prefs']->getValue('remote_cals'));
            foreach ($cals as $key => $cal) {
                if ($cal['url'] == $calUrl) {
                    $cals[$key]['name'] = $calName;
                    $cals[$key]['url'] = $calUrl;
                    $cals[$key]['user'] = $calUser;
                    $cals[$key]['password'] = $calPasswd;
                    break;
                }
            }
            $GLOBALS['prefs']->setValue('remote_cals', serialize($cals));
        }
    }

    /**
     * Update address book related preferences.
     *
     * @param Horde_Core_Prefs_Ui $ui  The UI object.
     *
     * @return boolean  True if preferences were updated.
     */
    protected function _prefsSourceselect($ui)
    {
        global $prefs;

        $data = Horde_Core_Prefs_Ui_Widgets::addressbooksUpdate($ui);
        $updated = false;

        if (isset($data['sources'])) {
            $prefs->setValue('search_sources', $data['sources']);
            $updated = true;
        }

        if (isset($data['fields'])) {
            $prefs->setValue('search_fields', $data['fields']);
            $updated = true;
        }

        return $updated;
    }

    /**
     * Removes user data.
     *
     * @param string $user  Name of user to remove data for.
     *
     * @throws Kronolith_Exception
     * @throws Horde_History_Exception
     * @throws InvalidArgumentException
     */
    public function removeUserData($user)
    {
        /* Remove all events owned by the user in all calendars. */
        $result = Kronolith::getDriver()->removeUserData($user);

        /* Get the user's default share */
        try {
            $share = $GLOBALS['kronolith_shares']->getShare($user);
            $result = $GLOBALS['kronolith_shares']->removeShare($share);
        } catch (Exception $e) {
            Horde::logMessage($e, 'ERR');
            throw $e;
        }

        /* Get a list of all shares this user has perms to and remove the
         * perms */
        try {
            $shares = $GLOBALS['kronolith_shares']->listShares($user);
            foreach ($shares as $share) {
                $share->removeUser($user);
            }
        } catch (Horde_Share_Exception $e) {
            Horde::logMessage($e, 'ERR');
            throw $e;
        }
    }

    /* Sidebar method. */

    /**
     * Add node(s) to the sidebar tree.
     *
     * @param Horde_Tree_Base $tree  Tree object.
     * @param string $parent         The current parent element.
     * @param array $params          Additional parameters.
     *
     * @throws Horde_Exception
     */
    public function sidebarCreate(Horde_Tree_Base $tree, $parent = null,
                                  array $params = array())
    {
        switch ($params['id']) {
        case 'alarms':
            try {
                $alarms = Kronolith::listAlarms(new Horde_Date($_SERVER['REQUEST_TIME']), $GLOBALS['display_calendars'], true);
            } catch (Kronolith_Exception $e) {
                return;
            }

            $alarmCount = 0;
            $alarmImg = Horde_Themes::img('alarm.png');
            $horde_alarm = $GLOBALS['injector']->getInstance('Horde_Alarm');

            foreach ($alarms as $calId => $calAlarms) {
                foreach ($calAlarms as $event) {
                    if ($horde_alarm->isSnoozed($event->uid, $GLOBALS['registry']->getAuth())) {
                        continue;
                    }
                    ++$alarmCount;
                    $tree->addNode(
                        $parent . $calId . $event->id,
                        $parent,
                        htmlspecialchars($event->getTitle()),
                        1,
                        false,
                        array(
                            'icon' => $alarmImg,
                            'url' => $event->getViewUrl()
                        )
                    );
                }
            }

            if ($GLOBALS['registry']->get('url', $parent)) {
                $purl = $GLOBALS['registry']->get('url', $parent);
            } elseif ($GLOBALS['registry']->get('status', $parent) == 'heading' ||
                      !$GLOBALS['registry']->get('webroot')) {
                $purl = null;
            } else {
                $purl = Horde::url($GLOBALS['registry']->getInitialPage($parent));
            }

            $pnode_name = $GLOBALS['registry']->get('name', $parent);
            if ($alarmCount) {
                $pnode_name = '<strong>' . $pnode_name . '</strong>';
            }

            $tree->addNode(
                $parent,
                $GLOBALS['registry']->get('menu_parent', $parent),
                $pnode_name,
                0,
                false,
                array(
                    'icon' => $GLOBALS['registry']->get('icon', $parent),
                    'url' => $purl,
                )
            );
            break;

        case 'menu':
            $menus = array(
                array('new', _("New Event"), 'new.png', Horde::url('new.php')),
                array('day', _("Day"), 'dayview.png', Horde::url('day.php')),
                array('work', _("Work Week"), 'workweekview.png', Horde::url('workweek.php')),
                array('week', _("Week"), 'weekview.png', Horde::url('week.php')),
                array('month', _("Month"), 'monthview.png', Horde::url('month.php')),
                array('year', _("Year"), 'yearview.png', Horde::url('year.php')),
                array('search', _("Search"), 'search.png', Horde::url('search.php'))
            );

            foreach ($menus as $menu) {
                $tree->addNode(
                    $parent . $menu[0],
                    $parent,
                    $menu[1],
                    1,
                    false,
                    array(
                        'icon' => Horde_Themes::img($menu[2]),
                        'url' => $menu[3]
                    )
                );
            }
            break;
        }
    }

    /**
     * Callback, called from common-template-mobile.inc that sets up the jquery
     * mobile init hanler.
     */
    public function mobileInitCallback()
    {
        $datejs = str_replace('_', '-', $GLOBALS['language']) . '.js';
        if (!file_exists($GLOBALS['registry']->get('jsfs', 'horde') . '/date/' . $datejs)) {
            $datejs = 'en-US.js';
        }

        Horde::addScriptFile('date/' . $datejs, 'horde');
        Horde::addScriptFile('date/date.js', 'horde');
        Horde::addScriptFile('mobile.js');
        require KRONOLITH_TEMPLATES . '/mobile/javascript_defs.php';

        /* Inline script. */
        Horde::addInlineScript(
          '$(window.document).bind("mobileinit", function() {
              $.mobile.page.prototype.options.backBtnText = "' . _("Back") .'";
              $.mobile.loadingMessage = "' . _("loading") . '";

              // Setup event bindings to populate views on pagebeforeshow
              KronolithMobile.date = new Date();
              $("#dayview").live("pagebeforeshow", function() {
                  KronolithMobile.view = "day";
                  $(".kronolithDayDate").html(KronolithMobile.date.toString("ddd") + " " + KronolithMobile.date.toString("d"));
                  KronolithMobile.loadEvents(KronolithMobile.date, KronolithMobile.date, "day");
              });

              $("#monthview").live("pagebeforeshow", function(event, ui) {
                KronolithMobile.view = "month";
                // (re)build the minical only if we need to
                if (!$(".kronolithMinicalDate").data("date") ||
                    ($(".kronolithMinicalDate").data("date").toString("M") != KronolithMobile.date.toString("M"))) {
                    KronolithMobile.moveToMonth(KronolithMobile.date);
                }
              });

              $("#eventview").live("pageshow", function(event, ui) {
                    KronolithMobile.view = "event";
              });

              // Set up overview
              $("#overview").live("pageshow", function(event, ui) {
                  KronolithMobile.view = "overview";
                  if (!KronolithMobile.haveOverview) {
                      KronolithMobile.loadEvents(KronolithMobile.date, KronolithMobile.date.clone().addDays(7), "overview");
                      KronolithMobile.haveOverview = true;
                  }
              });

           });'
        );
    }

    /**
     * Browse through Kronolith's object tree.
     *
     * @param string $path       The level of the tree to browse.
     * @param array $properties  The item properties to return. Defaults to 'name',
     *                           'icon', and 'browseable'.
     *
     * @return array  The contents of $path
     * @throws Kronolith_Exception
     */
    public function browse($path = '', $properties = array())
    {
        global $registry;

        // Default properties.
        if (!$properties) {
            $properties = array('name', 'icon', 'browseable');
        }

        if (substr($path, 0, 9) == 'kronolith') {
            $path = substr($path, 9);
        }
        $path = trim($path, '/');
        $parts = explode('/', $path);

        if (empty($path)) {
            // This request is for a list of all users who have calendars
            // visible to the requesting user.
            $calendars = Kronolith::listInternalCalendars(false, Horde_Perms::READ);
            $owners = array();
            foreach ($calendars as $calendar) {
                if ($calendar->get('owner') != $GLOBALS['registry']->getAuth() &&
                    !empty($GLOBALS['conf']['share']['hidden']) &&
                    !in_array($calendar->getName(), $GLOBALS['display_calendars'])) {
                    continue;
                }
                $owners[$calendar->get('owner')] = true;
            }

            $results = array();
            foreach (array_keys($owners) as $owner) {
                $path = 'kronolith/' . $owner;
                if (in_array('name', $properties)) {
                    $results[$path]['name'] = $owner;
                }
                if (in_array('icon', $properties)) {
                    $results[$path]['icon'] = Horde_Themes::img('user.png');
                }
                if (in_array('browseable', $properties)) {
                    $results[$path]['browseable'] = true;
                }
                if (in_array('contenttype', $properties)) {
                    $results[$path]['contenttype'] =
                        'httpd/unix-directory';
                }
                if (in_array('contentlength', $properties)) {
                    $results[$path]['contentlength'] = 0;
                }
                if (in_array('modified', $properties)) {
                    $results[$path]['modified'] =
                        $_SERVER['REQUEST_TIME'];
                }
                if (in_array('created', $properties)) {
                    $results[$path]['created'] = 0;
                }

                // CalDAV Properties from RFC 4791 and
                // draft-desruisseaux-caldav-sched-03
                $caldavns = 'urn:ietf:params:xml:ns:caldav';
                $kronolith_rpc_base = $GLOBALS['registry']->get('webroot', 'horde') . '/rpc/kronolith/';
                if (in_array($caldavns . ':calendar-home-set', $properties)) {
                    $results[$path][$caldavns . ':calendar-home-set'] =  Horde::url($kronolith_rpc_base . urlencode($owner), true);
                }

                if (in_array($caldavns . ':calendar-user-address-set', $properties)) {
                    // FIXME: Add the calendar owner's email address from
                    // their Horde Identity
                }
            }
            return $results;

        } elseif (count($parts) == 1) {
            // This request is for all calendars owned by the requested user
            $calendars = $GLOBALS['kronolith_shares']->listShares(
                $GLOBALS['registry']->getAuth(),
                array('perm' => Horde_Perms::SHOW,
                      'attributes' => $parts[0]));
            $results = array();
            foreach ($calendars as $calendarId => $calendar) {
                $retpath = 'kronolith/' . $parts[0] . '/' . $calendarId;
                if (in_array('name', $properties)) {
                    $results[$retpath]['name'] = sprintf(_("Events from %s"), $calendar->get('name'));
                    $results[$retpath . '.ics']['name'] = $calendar->get('name');
                }
                if (in_array('displayname', $properties)) {
                    $results[$retpath]['displayname'] = rawurlencode($calendar->get('name'));
                    $results[$retpath . '.ics']['displayname'] = rawurlencode($calendar->get('name')) . '.ics';
                }
                if (in_array('icon', $properties)) {
                    $results[$retpath]['icon'] = Horde_Themes::img('kronolith.png');
                    $results[$retpath . '.ics']['icon'] = Horde_Themes::img('mime/icalendar.png');
                }
                if (in_array('browseable', $properties)) {
                    $results[$retpath]['browseable'] = $calendar->hasPermission($GLOBALS['registry']->getAuth(), Horde_Perms::READ);
                    $results[$retpath . '.ics']['browseable'] = false;
                }
                if (in_array('contenttype', $properties)) {
                    $results[$retpath]['contenttype'] = 'httpd/unix-directory';
                    $results[$retpath . '.ics']['contenttype'] = 'text/calendar';
                }
                if (in_array('contentlength', $properties)) {
                    $results[$retpath]['contentlength'] = 0;
                    // FIXME: This is a hack.  If the content length is longer
                    // than the actual data then some WebDAV clients will
                    // report an error when the file EOF is received.  Ideally
                    // we should determine the actual size of the .ics and
                    // report it here, but the performance hit may be
                    // prohibitive.  This requires further investigation.
                    $results[$retpath . '.ics']['contentlength'] = 1;
                }
                if (in_array('modified', $properties)) {
                    $results[$retpath]['modified'] = $_SERVER['REQUEST_TIME'];
                    $results[$retpath . '.ics']['modified'] = $_SERVER['REQUEST_TIME'];
                }
                if (in_array('created', $properties)) {
                    $results[$retpath]['created'] = 0;
                    $results[$retpath . '.ics']['created'] = 0;
                }
            }
            return $results;

        } elseif (count($parts) == 2 &&
                  array_key_exists($parts[1], Kronolith::listInternalCalendars(false, Horde_Perms::READ))) {
            // This request is browsing into a specific calendar.  Generate
            // the list of items and represent them as files within the
            // directory.
            $kronolith_driver = Kronolith::getDriver(null, $parts[1]);
            $events = $kronolith_driver->listEvents();
            $icon = Horde_Themes::img('mime/icalendar.png');
            $results = array();
            foreach ($events as $dayevents) {
                foreach ($dayevents as $event) {
                    $key = 'kronolith/' . $path . '/' . $event->id;
                    if (in_array('name', $properties)) {
                        $results[$key]['name'] = $event->getTitle();
                    }
                    if (in_array('icon', $properties)) {
                        $results[$key]['icon'] = $icon;
                    }
                    if (in_array('browseable', $properties)) {
                        $results[$key]['browseable'] = false;
                    }
                    if (in_array('contenttype', $properties)) {
                        $results[$key]['contenttype'] = 'text/calendar';
                    }
                    if (in_array('contentlength', $properties)) {
                        // FIXME: This is a hack.  If the content length is
                        // longer than the actual data then some WebDAV
                        // clients will report an error when the file EOF is
                        // received.  Ideally we should determine the actual
                        // size of the data and report it here, but the
                        // performance hit may be prohibitive.  This requires
                        // further investigation.
                        $results[$key]['contentlength'] = 1;
                    }
                    if (in_array('modified', $properties)) {
                        $results[$key]['modified'] = $this->modified($event->uid);
                    }
                    if (in_array('created', $properties)) {
                        $results[$key]['created'] = $this->getActionTimestamp($event->uid, 'add');
                    }
                }
            }
            return $results;
        } else {
            // The only valid request left is for either a specific event or
            // for the entire calendar.
            if (count($parts) == 3 &&
                array_key_exists($parts[1], Kronolith::listInternalCalendars(false, Horde_Perms::READ))) {
                // This request is for a specific item within a given calendar.
                $event = Kronolith::getDriver(null, $parts[1])->getEvent($parts[2]);

                $result = array(
                    'data' => $this->export($event->uid, 'text/calendar'),
                    'mimetype' => 'text/calendar');
                $modified = $this->modified($event->uid);
                if (!empty($modified)) {
                    $result['mtime'] = $modified;
                }
                return $result;
            } elseif (count($parts) == 2 &&
                      substr($parts[1], -4, 4) == '.ics' &&
                      array_key_exists(substr($parts[1], 0, -4), Kronolith::listInternalCalendars(false, Horde_Perms::READ))) {
                // This request is for an entire calendar (calendar.ics).
                $ical_data = $this->exportCalendar(substr($parts[1], 0, -4), 'text/calendar');
                $result = array('data'          => $ical_data,
                                'mimetype'      => 'text/calendar',
                                'contentlength' => strlen($ical_data),
                                'mtime'         => $_SERVER['REQUEST_TIME']);

                return $result;
            } else {
                // All other requests are a 404: Not Found
                return false;
            }
        }
    }

        /**
     * Returns the last modification timestamp for the given uid.
     *
     * @param string $uid      The uid to look for.
     *
     * @return integer  The timestamp for the last modification of $uid.
     */
    public function modified($uid)
    {
        $modified = $this->getActionTimestamp($uid, 'modify');
        if (empty($modified)) {
            $modified = $this->getActionTimestamp($uid, 'add');
        }
        return $modified;
    }

    /**
     * Saves a file into the Kronolith tree.
     *
     * @param string $path          The path where to PUT the file.
     * @param string $content       The file content.
     * @param string $content_type  The file's content type.
     *
     * @return array  The event UIDs.
     * @throws Kronolith_Exception
     */
    public function put($path, $content, $content_type)
    {
        if (substr($path, 0, 9) == 'kronolith') {
            $path = substr($path, 9);
        }
        $path = trim($path, '/');
        $parts = explode('/', $path);

        if (count($parts) == 2 && substr($parts[1], -4) == '.ics') {
            // Workaround for WebDAV clients that are not smart enough to send
            // the right content type.  Assume text/calendar.
            if ($content_type == 'application/octet-stream') {
                $content_type = 'text/calendar';
            }
            $calendar = substr($parts[1], 0, -4);
        } elseif (count($parts) == 3) {
            $calendar = $parts[1];
            // Workaround for WebDAV clients that are not smart enough to send
            // the right content type.  Assume text/calendar.
            if ($content_type == 'application/octet-stream') {
                $content_type = 'text/calendar';
            }
        } else {
            throw new Kronolith_Exception("Invalid calendar data supplied.");
        }

        if (!array_key_exists($calendar, Kronolith::listInternalCalendars(false, Horde_Perms::EDIT))) {
            // FIXME: Should we attempt to create a calendar based on the
            // filename in the case that the requested calendar does not
            // exist?
            throw new Kronolith_Exception("Calendar does not exist or no permission to edit");
        }

        // Store all currently existings UIDs. Use this info to delete UIDs not
        // present in $content after processing.
        $ids = array();
        $uids_remove = array_flip($this->listUids($calendar));

        switch ($content_type) {
        case 'text/calendar':
        case 'text/x-vcalendar':
            $iCal = new Horde_Icalendar();
            if (!($content instanceof Horde_Icalendar_Vevent)) {
                if (!$iCal->parsevCalendar($content)) {
                    throw new Kronolith_Exception(_("There was an error importing the iCalendar data."));
                }
            } else {
                $iCal->addComponent($content);
            }

            $kronolith_driver = Kronolith::getDriver();
            foreach ($iCal->getComponents() as $content) {
                if ($content instanceof Horde_Icalendar_Vevent) {
                    $event = $kronolith_driver->getEvent();
                    $event->fromiCalendar($content);
                    $uid = $event->uid;
                    // Remove from uids_remove list so we won't delete in the
                    // end.
                    if (isset($uids_remove[$uid])) {
                        unset($uids_remove[$uid]);
                    }
                    try {
                        $existing_event = $kronolith_driver->getByUID($uid, array($calendar));
                        // Check if our event is newer then the existing - get
                        // the event's history.
                        $created = $modified = null;
                        try {
                            $log = $GLOBALS['injector']->getInstance('Horde_History')->getHistory('kronolith:' . $calendar . ':' . $uid);
                            foreach ($log as $entry) {
                                switch ($entry['action']) {
                                case 'add':
                                    $created = $entry['ts'];
                                    break;

                                case 'modify':
                                    $modified = $entry['ts'];
                                    break;
                                }
                            }
                        } catch (Exception $e) {}
                        if (empty($modified) && !empty($created)) {
                            $modified = $created;
                        }
                        if (!empty($modified) &&
                            $modified >= $content->getAttribute('LAST-MODIFIED')) {
                                // LAST-MODIFIED timestamp of existing entry
                                // is newer: don't replace it.
                                continue;
                            }

                        // Don't change creator/owner.
                        $event->creator = $existing_event->creator;
                    } catch (Horde_Exception_NotFound $e) {}

                    // Save entry.
                    $saved = $event->save();
                    $ids[] = $event->uid;
                }
            }
            break;

        default:
            throw new Kronolith_Exception(sprintf(_("Unsupported Content-Type: %s"), $content_type));
        }

        if (array_key_exists($calendar, Kronolith::listInternalCalendars(false, Horde_Perms::DELETE))) {
            foreach (array_keys($uids_remove) as $uid) {
                $this->delete($uid);
            }
        }

        return $ids;
    }

    /**
     * Deletes a file from the Kronolith tree.
     *
     * @param string $path  The path to the file.
     *
     * @throws Kronolith_Exception
     */
    public function path_delete($path)
    {
        if (substr($path, 0, 9) == 'kronolith') {
            $path = substr($path, 9);
        }
        $path = trim($path, '/');
        $parts = explode('/', $path);

        if (substr($parts[1], -4) == '.ics') {
            $calendarId = substr($parts[1], 0, -4);
        } else {
            $calendarId = $parts[1];
        }

        if (!(count($parts) == 2 || count($parts) == 3) ||
            !array_key_exists($calendarId, Kronolith::listInternalCalendars(false, Horde_Perms::DELETE))) {
                throw new Kronolith_Exception("Calendar does not exist or no permission to delete");
            }

        if (count($parts) == 3) {
            // Delete just a single entry
            return Kronolith::getDriver(null, $calendarId)->deleteEvent($parts[2]);
        } else {
            // Delete the entire calendar
            try {
                Kronolith::getDriver()->delete($calendarId);
                // Remove share and all groups/permissions.
                $share = $GLOBALS['kronolith_shares']->getShare($calendarId);
                $result = $GLOBALS['kronolith_shares']->removeShare($share);
            } catch (Exception $e) {
                throw new Kronolith_Exception(sprintf(_("Unable to delete calendar \"%s\": %s"), $calendarId, $e->getMessage()));
            }
        }
    }

}
