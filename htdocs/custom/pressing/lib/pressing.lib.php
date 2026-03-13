<?php

/**
 * Prepare admin pages header.
 */
function pressingAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load('pressing@pressing');

    $h = 0;
    $head = array();

    $head[$h][0] = DOL_URL_ROOT.'/custom/pressing/admin/setup.php';
    $head[$h][1] = $langs->trans('Settings');
    $head[$h][2] = 'settings';
    $h++;

    complete_head_from_modules($conf, $langs, null, $head, $h, 'pressing_admin');

    return $head;
}

/**
 * Return order tabs.
 */
function pressingOrderPrepareHead($object)
{
    global $langs;

    $langs->load('pressing@pressing');
    $h = 0;
    $head = array();

    $head[$h][0] = DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$object->id;
    $head[$h][1] = $langs->trans('Card');
    $head[$h][2] = 'card';
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/custom/pressing/order/ticket.php?id='.$object->id;
    $head[$h][1] = $langs->trans('Ticket');
    $head[$h][2] = 'ticket';

    return $head;
}
