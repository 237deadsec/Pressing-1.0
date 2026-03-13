<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once __DIR__.'/../class/pressingorder.class.php';

$langs->loadLangs(array('pressing@pressing', 'companies'));

if (!$user->rights->pressing->read) {
    accessforbidden();
}

$search_ref = GETPOST('search_ref', 'alpha');
$search_socid = GETPOSTINT('search_socid');
$search_status = GETPOST('search_status', 'int');
$search_date = GETPOST('search_date', 'alpha');

$sql = "SELECT o.rowid, o.ref, o.date_deposit, o.date_expected_pickup, o.total_ht, o.status, s.rowid as socid, s.nom as socname";
$sql .= " FROM ".MAIN_DB_PREFIX."pressing_order as o";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = o.fk_soc";
$sql .= " WHERE o.entity IN (".getEntity('pressing_order').")";
if ($search_ref) {
    $sql .= " AND o.ref LIKE '%".$db->escape($search_ref)."%'";
}
if ($search_socid > 0) {
    $sql .= " AND o.fk_soc = ".((int) $search_socid);
}
if ($search_status !== '') {
    $sql .= " AND o.status = ".((int) $search_status);
}
if ($search_date) {
    $sql .= " AND date(o.date_deposit) = '".$db->escape($search_date)."'";
}
$sql .= " ORDER BY o.datec DESC";

$resql = $db->query($sql);

llxHeader('', $langs->trans('Orders'));
print load_fiche_titre($langs->trans('LaundryOrders'));

print '<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans('TicketNumber').'</td>';
print '<td>'.$langs->trans('Customer').'</td>';
print '<td>'.$langs->trans('DepositDate').'</td>';
print '<td>'.$langs->trans('ExpectedPickupDate').'</td>';
print '<td>'.$langs->trans('Status').'</td>';
print '<td class="right">'.$langs->trans('AmountHT').'</td>';
print '</tr>';

print '<tr class="liste_titre_filter">';
print '<td><input type="text" name="search_ref" value="'.dol_escape_htmltag($search_ref).'"></td>';
print '<td>';
$form = new Form($db);
print $form->select_company($search_socid, 'search_socid', '', 'SelectThirdParty', 0, 0, null, 0, 'minwidth200');
print '</td>';
print '<td><input type="date" name="search_date" value="'.dol_escape_htmltag($search_date).'"></td>';
print '<td></td>';
print '<td><select name="search_status">';
print '<option value=""></option>';
$statusLabels = array(
    0 => 'PressingStatusReceived',
    1 => 'PressingStatusWashing',
    2 => 'PressingStatusIroning',
    3 => 'PressingStatusReady',
    4 => 'PressingStatusDelivered',
);
foreach ($statusLabels as $status => $labelKey) {
    print '<option value="'.$status.'"'.(((string) $search_status === (string) $status) ? ' selected' : '').'>'.dol_escape_htmltag($langs->trans($labelKey)).'</option>';
}
print '</select></td>';
print '<td class="right"><button class="button" type="submit">'.$langs->trans('Search').'</button></td>';
print '</tr>';

if ($resql) {
    while ($obj = $db->fetch_object($resql)) {
        $o = new PressingOrder($db);
        $o->status = (int) $obj->status;
        print '<tr class="oddeven">';
        print '<td><a href="'.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$obj->rowid.'">'.dol_escape_htmltag($obj->ref).'</a></td>';
        print '<td><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$obj->socid.'">'.dol_escape_htmltag($obj->socname).'</a></td>';
        print '<td>'.dol_print_date($db->jdate($obj->date_deposit), 'day').'</td>';
        print '<td>'.dol_print_date($db->jdate($obj->date_expected_pickup), 'day').'</td>';
        print '<td>'.$o->getStatusLabel().'</td>';
        print '<td class="right">'.price($obj->total_ht).'</td>';
        print '</tr>';
    }
}

print '</table></div></form>';

llxFooter();
$db->close();
