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
require 'module/iMSCP/Core/src/Functions/Tickets.php';

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(
    \iMSCP\Core\Events::onResellerScriptStart, \iMSCP\Core\Application::getInstance()->getApplicationEvent()
);

check_login('reseller');
resellerHasFeature('support') or showBadRequestErrorPage();

if (!hasTicketSystem($_SESSION['user_id'])) {
    redirectTo('index.php');
}

if (isset($_POST['uaction'])) {
    if (empty($_POST['subject'])) {
        set_page_message(tr('You must specify a subject.'), 'error');
    } elseif (empty($_POST['user_message'])) {
        set_page_message(tr('You must specify a message.'), 'error');
    } else {
        createTicket($_SESSION['user_id'], $_SESSION['user_created_by'], $_POST['urgency'], $_POST['subject'], $_POST['user_message'], 2);
        redirectTo('ticket_system.php');
    }
}

$userdata = [
    'OPT_URGENCY_1' => '',
    'OPT_URGENCY_2' => '',
    'OPT_URGENCY_3' => '',
    'OPT_URGENCY_4' => ''
];

if (isset($_POST['urgency'])) {
    $userdata['URGENCY'] = intval($_POST['urgency']);
} else {
    $userdata['URGENCY'] = 2;
}

$cfg = \iMSCP\Core\Application::getInstance()->getConfig();

switch ($userdata['URGENCY']) {
    case 1:
        $userdata['OPT_URGENCY_1'] = $cfg['HTML_SELECTED'];
        break;
    case 3:
        $userdata['OPT_URGENCY_3'] = $cfg['HTML_SELECTED'];
        break;
    case 4:
        $userdata['OPT_URGENCY_4'] = $cfg['HTML_SELECTED'];
        break;
    default:
        $userdata['OPT_URGENCY_2'] = $cfg['HTML_SELECTED'];
}

$userdata['SUBJECT'] = isset($_POST['subject']) ? clean_input($_POST['subject'], true) : '';
$userdata['USER_MESSAGE'] = isset($_POST['user_message'])
    ? clean_input($_POST['user_message'], true) : '';

$tpl = new \iMSCP\Core\Template\TemplateEngine();
$tpl->defineDynamic([
    'layout' => 'shared/layouts/ui.tpl',
    'page' => 'reseller/ticket_create.tpl',
    'page_message' => 'layout'
]);

$tpl->assign([
    'TR_PAGE_TITLE' => tr('Reseller / Support / New Ticket'),
    'TR_NEW_TICKET' => tr('New ticket'),
    'TR_LOW' => tr('Low'),
    'TR_MEDIUM' => tr('Medium'),
    'TR_HIGH' => tr('High'),
    'TR_VERY_HIGH' => tr('Very high'),
    'TR_URGENCY' => tr('Priority'),
    'TR_EMAIL' => tr('Email'),
    'TR_SUBJECT' => tr('Subject'),
    'TR_YOUR_MESSAGE' => tr('Message'),
    'TR_CREATE' => tr('Create'),
    'TR_OPEN_TICKETS' => tr('Open tickets'),
    'TR_CLOSED_TICKETS' => tr('Closed tickets')
]);

$tpl->assign($userdata);

generateNavigation($tpl);
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');

\iMSCP\Core\Application::getInstance()->getEventManager()->trigger(\iMSCP\Core\Events::onResellerScriptEnd, null, [
    'templateEngine' => $tpl
]);

$tpl->prnt();

unsetMessages();
