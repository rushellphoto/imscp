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
 * Main
 */

require '../../application.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
    \iMSCP\Core\Events::onClientScriptStart, \iMSCP\Core\Application::getInstance()->getApplicationEvent()
);

check_login('user');
customerHasFeature('protected_areas') or showBadRequestErrorPage();

/**
 * @todo check queries if any of them use db prepared statements
 */

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id = intval($_GET['id']);
    $domainId = get_user_domain_id($_SESSION['user_id']);
    $stmt = exec_query('SELECT `status` FROM `htaccess` WHERE `id` = ? AND `dmn_id` = ?', [$id, $domainId]);

    if (!$stmt->rowCount()) {
        showBadRequestErrorPage();
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['status'] !== 'ok') {
        set_page_message(tr("Protected area status should be 'OK' if you want to delete it."), 'error');
        redirectTo('protected_areas.php');
    }

    exec_query("UPDATE `htaccess` SET `status` = 'todelete' WHERE `id` = ? AND `dmn_id` = ?", [$id, $domainId]);
    send_request();
    write_log($_SESSION['user_logged'] . ": deleted protected area with ID: " . $_GET['id'], E_USER_NOTICE);
    set_page_message(tr('Protected area successfully scheduled for deletion.'), 'success');
    redirectTo('protected_areas.php');
}

set_page_message(tr('You do not have sufficient permissions to perform this operation.'), 'error');
redirectTo('protected_areas.php');
