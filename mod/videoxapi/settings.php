<?php
/**
 * Plugin administration pages are defined here.
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    require_once($CFG->dirroot . '/mod/videoxapi/lib/xapi/ConfigManager.php');

    // 1) Önce 'Activity modules' altına kendi kategori başlığımızı ekleyelim
    $ADMIN->add('modsettings',
        new admin_category(
            'mod_videoxapi',                     // benzersiz kategori adı
            get_string('pluginname', 'videoxapi') // eklenti adı
        )
    );

    // 2) Kendi ayar sayfamızı bu kategori altına ekleyelim
    $settings = new admin_settingpage(
        'modsetting_videoxapi',                // benzersiz ayar sayfası anahtarı
        get_string('settings', 'videoxapi')     // Ayar sayfası başlığı
    );

    // xAPI Configuration section.
    $settings->add(new admin_setting_heading('videoxapi_xapi_heading',
        get_string('xapiconfig', 'videoxapi'),
        get_string('xapiconfig_desc', 'videoxapi')
    ));

    // Enable xAPI tracking.
    $settings->add(new admin_setting_configcheckbox('mod_videoxapi/xapi_enabled',
        get_string('xapienabled', 'videoxapi'),
        get_string('xapienabled_desc', 'videoxapi'),
        0
    ));    // LRS endpoint URL.
    $settings->add(new admin_setting_configtext('mod_videoxapi/lrs_endpoint',
        get_string('lrsendpoint', 'videoxapi'),
        get_string('lrsendpoint_desc', 'videoxapi'),
        '',
        PARAM_URL
    ));

    // LRS username.
    $settings->add(new admin_setting_configpasswordunmask('mod_videoxapi/lrs_username',
        get_string('lrsusername', 'videoxapi'),
        get_string('lrsusername_desc', 'videoxapi'),
        ''
    ));

    // LRS password.
    $settings->add(new admin_setting_configpasswordunmask('mod_videoxapi/lrs_password',
        get_string('lrspassword', 'videoxapi'),
        get_string('lrspassword_desc', 'videoxapi'),
        ''
    ));

    // LRS authentication method.
    $authmethods = [
        'basic' => get_string('authbasic', 'videoxapi'),
        'oauth' => get_string('authoauth', 'videoxapi')
    ];
    $settings->add(new admin_setting_configselect('mod_videoxapi/lrs_auth_method',
        get_string('lrsauthmethod', 'videoxapi'),
        get_string('lrsauthmethod_desc', 'videoxapi'),
        'basic',
        $authmethods
    ));

    // Queue processing section.
    $settings->add(new admin_setting_heading('videoxapi_queue_heading',
        get_string('queueconfig', 'videoxapi'),
        get_string('queueconfig_desc', 'videoxapi')
    ));    // Enable queue processing.
    $settings->add(new admin_setting_configcheckbox('mod_videoxapi/queue_enabled',
        get_string('queueenabled', 'videoxapi'),
        get_string('queueenabled_desc', 'videoxapi'),
        1
    ));

    // Queue processing frequency.
    $frequencies = [
        60 => get_string('every1minute', 'videoxapi'),
        300 => get_string('every5minutes', 'videoxapi'),
        900 => get_string('every15minutes', 'videoxapi'),
        1800 => get_string('every30minutes', 'videoxapi'),
        3600 => get_string('every1hour', 'videoxapi')
    ];
    $settings->add(new admin_setting_configselect('mod_videoxapi/queue_frequency',
        get_string('queuefrequency', 'videoxapi'),
        get_string('queuefrequency_desc', 'videoxapi'),
        300,
        $frequencies
    ));

    // Queue batch size.
    $settings->add(new admin_setting_configtext('mod_videoxapi/queue_batch_size',
        get_string('queuebatchsize', 'videoxapi'),
        get_string('queuebatchsize_desc', 'videoxapi'),
        50,
        PARAM_INT
    ));

    // Connection timeout.
    $settings->add(new admin_setting_configtext('mod_videoxapi/connection_timeout',
        get_string('connectiontimeout', 'videoxapi'),
        get_string('connectiontimeout_desc', 'videoxapi'),
        30,
        PARAM_INT
    ));    // Test connection link.
    $settings->add(new admin_setting_heading('videoxapi_testlink',
        get_string('testconnection', 'videoxapi'),
        html_writer::link(
            new moodle_url('/mod/videoxapi/admin/test_connection.php'),
            get_string('testconnectionbutton', 'videoxapi'),
            ['class' => 'btn btn-secondary']
        )
    ));

    // Ayar sayfasını kategoriye ekle
    $ADMIN->add('mod_videoxapi', $settings);

    // Test connection external page'i de ekleyelim
    $ADMIN->add('mod_videoxapi', new admin_externalpage(
        'mod_videoxapi_testconnection',
        get_string('testconnection', 'videoxapi'),
        new moodle_url('/mod/videoxapi/admin/test_connection.php'),
        'moodle/site:config'
    ));
}