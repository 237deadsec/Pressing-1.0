<?php
require '../../main.inc.php';
require_once __DIR__.'/class/pressingorder.class.php';

$langs->loadLangs(array('pressing@pressing', 'bills', 'companies'));

if (!$user->rights->pressing->read) {
    accessforbidden();
}

$nbToday = 0;
$nbReady = 0;
$nbPending = 0;
$dailyRevenue = 0;

$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."pressing_order WHERE entity IN (".getEntity('pressing_order').") AND date(date_deposit) = date(NOW())";
$resql = $db->query($sql);
if ($resql) {
    $nbToday = (int) $db->fetch_object($resql)->nb;
}

$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."pressing_order WHERE entity IN (".getEntity('pressing_order').") AND status = ".PressingOrder::STATUS_READY;
$resql = $db->query($sql);
if ($resql) {
    $nbReady = (int) $db->fetch_object($resql)->nb;
}

$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."pressing_order WHERE entity IN (".getEntity('pressing_order').") AND status < ".PressingOrder::STATUS_DELIVERED;
$resql = $db->query($sql);
if ($resql) {
    $nbPending = (int) $db->fetch_object($resql)->nb;
}

$sql = "SELECT SUM(total_ht) as total FROM ".MAIN_DB_PREFIX."pressing_order WHERE entity IN (".getEntity('pressing_order').") AND date(date_deposit) = date(NOW())";
$resql = $db->query($sql);
if ($resql) {
    $dailyRevenue = (float) $db->fetch_object($resql)->total;
}

llxHeader('', $langs->trans('PressingDashboard'));

print load_fiche_titre($langs->trans('PressingDashboard'));
print '<div class="fichecenter pressing-dashboard">';
print '<div class="dashboard-item"><span class="label">'.$langs->trans('TodayDeposits').'</span><span class="value">'.$nbToday.'</span></div>';
print '<div class="dashboard-item"><span class="label">'.$langs->trans('ReadyItems').'</span><span class="value">'.$nbReady.'</span></div>';
print '<div class="dashboard-item"><span class="label">'.$langs->trans('PendingDeliveries').'</span><span class="value">'.$nbPending.'</span></div>';
print '<div class="dashboard-item"><span class="label">'.$langs->trans('DailyRevenue').'</span><span class="value">'.price($dailyRevenue).'</span></div>';
print '</div>';

print '<div class="tabsAction">';
print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/pressing/order/card.php?action=create">'.$langs->trans('NewOrder').'</a>';
print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/pressing/order/list.php">'.$langs->trans('Orders').'</a>';
print '</div>';

llxFooter();
$db->close();
