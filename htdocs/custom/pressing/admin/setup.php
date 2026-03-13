<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once __DIR__.'/../lib/pressing.lib.php';

$langs->loadLangs(array('admin', 'pressing@pressing'));

if (!$user->admin && !$user->rights->pressing->setup) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
if ($action === 'set') {
    dolibarr_set_const($db, 'PRESSING_PRICE_PER_KG', GETPOST('PRESSING_PRICE_PER_KG', 'alphanohtml'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'PRESSING_DEFAULT_PAYMENTMODE', GETPOST('PRESSING_DEFAULT_PAYMENTMODE', 'aZ09'), 'chaine', 0, '', $conf->entity);
    setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
}

llxHeader('', $langs->trans('PressingSetup'));

$form = new Form($db);
$head = pressingAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans('Module106500Name'), -1, 'generic');

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="action" value="set">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td colspan="2">'.$langs->trans('Settings').'</td></tr>';
print '<tr class="oddeven"><td>'.$langs->trans('DefaultPricePerKg').'</td><td><input type="number" step="0.01" name="PRESSING_PRICE_PER_KG" value="'.getDolGlobalString('PRESSING_PRICE_PER_KG', '0').'"></td></tr>';
print '<tr class="oddeven"><td>'.$langs->trans('DefaultPaymentMode').'</td><td><select name="PRESSING_DEFAULT_PAYMENTMODE"><option value="LIQ">'.$langs->trans('Cash').'</option><option value="CB">'.$langs->trans('CreditCard').'</option><option value="MOMO">Mobile Money</option></select></td></tr>';
print '</table>';
print '<div class="center"><input type="submit" class="button button-save" value="'.$langs->trans('Save').'" ></div>';
print '</form>';

print dol_get_fiche_end();
llxFooter();
$db->close();
