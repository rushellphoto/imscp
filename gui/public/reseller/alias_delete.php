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
    \iMSCP\Core\Events::onResellerScriptStart, \iMSCP\Core\Application::getInstance()->getApplicationEvent()
);

check_login('reseller');

if (resellerHasFeature('domain_aliases') && isset($_GET['id'])) {
    $alsId = intval($_GET['id']);
    $stmt = exec_query(
        '
            SELECT
                alias_name
            FROM
                domain_aliasses
            INNER JOIN
                domain USING (domain_id)
            INNER JOIN
                admin ON(admin_id = domain_admin_id)
            WHERE
                alias_id = ?
            AND
                created_by = ?
        ',
        [$alsId, $_SESSION['user_id']]
    );

    if ($stmt->rowCount()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        deleteDomainAlias($alsId, $row['alias_name']);
        redirectTo('alias.php');
    }
}

showBadRequestErrorPage();
