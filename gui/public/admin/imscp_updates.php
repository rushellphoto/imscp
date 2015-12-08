<?php
/**
 * i-MSCP - internet Multi Server Control Panel
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2015 by
 * i-MSCP - internet Multi Server Control Panel. All Rights Reserved.
 */

/***********************************************************************************************************************
 * Functions
 */

/**
 * Generate page
 *
 * @param  \iMSCP\Core\Template\TemplateEngine $tpl
 * @return void
 */
function admin_generatePage($tpl)
{
    $cfg = \iMSCP\Core\Application::getInstance()->getConfig();

    if (!isset($cfg['CHECK_FOR_UPDATES']) || !$cfg['CHECK_FOR_UPDATES']) {
        set_page_message(tr('i-MSCP version update checking is disabled'), 'static_warning');
    } else {
        $updateVersion = \iMSCP\Core\Updater\VersionUpdater::getInstance();

        if ($updateVersion->isAvailableUpdate()) {
            if (($updateInfo = $updateVersion->getUpdateInfo())) {
                $date = new DateTime($updateInfo['published_at']);
                $tpl->assign([
                    'TR_UPDATE_INFO' => tr('Update info'),
                    'TR_RELEASE_VERSION' => tr('Release version'),
                    'RELEASE_VERSION' => tohtml($updateInfo['tag_name']),
                    'TR_RELEASE_DATE' => tr('Release date'),
                    'RELEASE_DATE' => tohtml($date->format($cfg['DATE_FORMAT'])),
                    'TR_RELEASE_DESCRIPTION' => tr('Release description'),
                    'RELEASE_DESCRIPTION' => tohtml($updateInfo['body']),
                    'TR_DOWNLOAD_LINKS' => tr('Download links'),
                    'TR_DOWNLOAD_ZIP' => tr('Download ZIP'),
                    'TR_DOWNLOAD_TAR' => tr('Download TAR'),
                    'TARBALL_URL' => tohtml($updateInfo['tarball_url']),
                    'ZIPBALL_URL' => tohtml($updateInfo['zipball_url'])
                ]);
                return;
            }

            set_page_message($updateVersion->getError(), 'error');
        } elseif ($updateVersion->getError()) {
            set_page_message($updateVersion, 'error');
        } else {
            set_page_message(tr('No update available'), 'static_info');
        }
    }

    $tpl->assign('UPDATE_INFO', '');
}

/***********************************************************************************************************************
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
    \iMSCP\Core\Events::onAdminScriptStart, \iMSCP\Core\Application::getInstance()->getApplicationEvent()
);

check_login('admin');

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->defineDynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'admin/imscp_updates.tpl',
    'page_message' => 'layout',
    'update_info' => 'page'
]);
$tpl->assign('TR_PAGE_TITLE', tr('Admin / System Tools / i-MSCP Updates'));

generateNavigation($tpl);
admin_generatePage($tpl);
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');
\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onAdminScriptEnd, null, [
    'templateEngine' => $tpl
]);
$tpl->prnt();

unsetMessages();
