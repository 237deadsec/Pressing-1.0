<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once __DIR__.'/../class/pressingorder.class.php';
require_once __DIR__.'/../class/pressingitem.class.php';
require_once __DIR__.'/../class/pressingprice.class.php';
require_once __DIR__.'/../lib/pressing.lib.php';

$langs->loadLangs(array('pressing@pressing', 'bills', 'companies', 'products'));

$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');

if (!$user->rights->pressing->read) {
    accessforbidden();
}

$object = new PressingOrder($db);
if ($id > 0) {
    $object->fetch($id);
}

if ($action === 'create' && GETPOST('cancel', 'alpha')) {
    header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/list.php');
    exit;
}

if ($action === 'add' && $user->rights->pressing->write) {
    $object->fk_soc = GETPOSTINT('fk_soc');
    $object->date_deposit = dol_mktime(0, 0, 0, GETPOSTINT('depositmonth'), GETPOSTINT('depositday'), GETPOSTINT('deposityear'));
    $object->date_expected_pickup = dol_mktime(0, 0, 0, GETPOSTINT('pickupmonth'), GETPOSTINT('pickupday'), GETPOSTINT('pickupyear'));
    $object->service_type = GETPOST('service_type', 'aZ09');
    $object->weight_kg = GETPOST('weight_kg', 'alphanohtml');
    $object->price_per_kg = GETPOST('price_per_kg', 'alphanohtml');
    $object->note_private = GETPOST('note_private', 'restricthtml');
    $object->status = PressingOrder::STATUS_RECEIVED;
    $object->payment_mode = GETPOST('payment_mode', 'aZ09');
    $object->amount_paid = GETPOST('amount_paid', 'alphanohtml');

    $res = $object->create($user);
    if ($res > 0) {
        header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$object->id);
        exit;
    }

    setEventMessages($object->error, $object->errors, 'errors');
}

if ($action === 'additem' && $id > 0 && $user->rights->pressing->write) {
    $item = new PressingItem($db);
    $item->fk_order = $object->id;
    $item->label = GETPOST('item_label', 'alphanohtml');
    $item->service_action = GETPOST('service_action', 'aZ09');
    $item->qty = GETPOSTINT('qty');
    $item->unit_price = GETPOST('unit_price', 'alphanohtml');
    $item->create($user);

    $db->query("UPDATE ".MAIN_DB_PREFIX."pressing_order SET total_ht = (SELECT COALESCE(SUM(total_ht),0) FROM ".MAIN_DB_PREFIX."pressing_items WHERE fk_order = ".((int) $object->id).") + IF(service_type='weight', weight_kg*price_per_kg, 0) WHERE rowid = ".((int) $object->id));

    header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$object->id);
    exit;
}

if ($action === 'setstatus' && $id > 0 && $user->rights->pressing->write) {
    $status = GETPOSTINT('new_status');
    $db->query("UPDATE ".MAIN_DB_PREFIX."pressing_order SET status = ".((int) $status)." WHERE rowid = ".((int) $object->id));
    header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$object->id);
    exit;
}

if ($action === 'createinvoice' && $id > 0 && $user->rights->pressing->write) {
    $invoice = new Facture($db);
    $invoice->socid = $object->fk_soc;
    $invoice->type = Facture::TYPE_STANDARD;
    $invoice->date = dol_now();
    $invoice->cond_reglement_id = 1;
    $invoice->mode_reglement_code = $object->payment_mode ?: getDolGlobalString('PRESSING_DEFAULT_PAYMENTMODE', 'LIQ');
    $invoiceid = $invoice->create($user);

    if ($invoiceid > 0) {
        if ($object->service_type === 'weight' && $object->weight_kg > 0) {
            $invoice->addline(
                $langs->trans('LaundryByWeight')." - ".$object->weight_kg." kg",
                $object->weight_kg * $object->price_per_kg,
                0,
                1,
                0,
                0,
                0,
                0,
                'HT',
                0,
                0,
                0,
                -1,
                0,
                0,
                '',
                'pressing'
            );
        }

        $items = $object->fetchItems();
        foreach ($items as $it) {
            $invoice->addline(
                $it->label.' '.$it->service_action.' x'.$it->qty,
                $it->unit_price,
                $it->qty,
                0,
                0,
                0,
                0,
                0,
                'HT',
                0,
                0,
                0,
                -1,
                0,
                0,
                '',
                'pressing'
            );
        }

        $db->query("UPDATE ".MAIN_DB_PREFIX."pressing_order SET fk_facture = ".((int) $invoiceid)." WHERE rowid = ".((int) $object->id));
        setEventMessages($langs->trans('InvoiceCreated'), null, 'mesgs');
    } else {
        setEventMessages($invoice->error, $invoice->errors, 'errors');
    }

    header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$object->id);
    exit;
}

$form = new Form($db);
$formcompany = new FormCompany($db);

llxHeader('', $langs->trans('PressingOrder'));

if ($action === 'create') {
    print load_fiche_titre($langs->trans('NewLaundryIntake'));
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="action" value="add">';
    print '<table class="border centpercent">';
    print '<tr><td class="titlefield fieldrequired">'.$langs->trans('Customer').'</td><td>';
    print $form->select_company(0, 'fk_soc', '', 'SelectThirdParty', 1);
    print '</td></tr>';
    print '<tr><td class="fieldrequired">'.$langs->trans('DepositDate').'</td><td>'.$form->selectDate(dol_now(), 'deposit', 0, 0, 0, 'create', 1, 1).'</td></tr>';
    print '<tr><td>'.$langs->trans('ExpectedPickupDate').'</td><td>'.$form->selectDate(dol_time_plus_duree(dol_now(), 2, 'd'), 'pickup', 0, 0, 0, 'create', 1, 1).'</td></tr>';
    print '<tr><td>'.$langs->trans('ServiceType').'</td><td><select name="service_type"><option value="weight">'.$langs->trans('ByKg').'</option><option value="item">'.$langs->trans('ByItem').'</option></select></td></tr>';
    print '<tr><td>'.$langs->trans('WeightKg').'</td><td><input type="number" step="0.01" name="weight_kg" value="0"></td></tr>';
    print '<tr><td>'.$langs->trans('PricePerKg').'</td><td><input type="number" step="0.01" name="price_per_kg" value="'.price2num(getDolGlobalString('PRESSING_PRICE_PER_KG', '0')).'"></td></tr>';
    print '<tr><td>'.$langs->trans('PaymentMode').'</td><td><select name="payment_mode"><option value="LIQ">'.$langs->trans('Cash').'</option><option value="VIR">'.$langs->trans('BankTransfer').'</option><option value="CB">'.$langs->trans('CreditCard').'</option><option value="MOMO">Mobile Money</option></select></td></tr>';
    print '<tr><td>'.$langs->trans('AmountPaid').'</td><td><input type="number" step="0.01" name="amount_paid" value="0"></td></tr>';
    print '<tr><td>'.$langs->trans('Notes').'</td><td><textarea name="note_private" class="flat" rows="4"></textarea></td></tr>';
    print '</table>';
    print '<div class="center">';
    print '<input type="submit" class="button button-save" value="'.$langs->trans('Create').'">';
    print '&nbsp;<input type="submit" class="button button-cancel" name="cancel" value="'.$langs->trans('Cancel').'">';
    print '</div>';
    print '</form>';
} elseif ($object->id > 0) {
    $head = pressingOrderPrepareHead($object);
    print dol_get_fiche_head($head, 'card', $langs->trans('PressingOrder'), -1, 'object_generic');

    print '<table class="border centpercent">';
    print '<tr><td class="titlefield">'.$langs->trans('TicketNumber').'</td><td>'.$object->ref.'</td></tr>';
    print '<tr><td>'.$langs->trans('Customer').'</td><td><a href="'.DOL_URL_ROOT.'/societe/card.php?socid='.$object->fk_soc.'">'.$object->fk_soc.'</a></td></tr>';
    print '<tr><td>'.$langs->trans('DepositDate').'</td><td>'.dol_print_date($object->date_deposit, 'day').'</td></tr>';
    print '<tr><td>'.$langs->trans('ExpectedPickupDate').'</td><td>'.dol_print_date($object->date_expected_pickup, 'day').'</td></tr>';
    print '<tr><td>'.$langs->trans('ServiceType').'</td><td>'.$object->service_type.'</td></tr>';
    print '<tr><td>'.$langs->trans('Status').'</td><td>'.$object->getStatusLabel().'</td></tr>';
    print '<tr><td>'.$langs->trans('PaymentMode').'</td><td>'.$object->payment_mode.'</td></tr>';
    print '<tr><td>'.$langs->trans('AmountPaid').'</td><td>'.price($object->amount_paid).'</td></tr>';
    print '<tr><td>'.$langs->trans('AmountHT').'</td><td>'.price($object->total_ht).'</td></tr>';
    print '</table>';

    print '<div class="tabsAction">';
    if (empty($object->fk_facture)) {
        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=createinvoice">'.$langs->trans('CreateInvoice').'</a>';
    } else {
        print '<a class="butActionRefused" href="'.DOL_URL_ROOT.'/compta/facture/card.php?facid='.$object->fk_facture.'">'.$langs->trans('Invoice').'</a>';
    }
    print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/pressing/order/ticket.php?id='.$object->id.'">'.$langs->trans('PrintTicket').'</a>';
    print '</div>';

    print '<h3>'.$langs->trans('Workflow').'</h3>';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
    print '<input type="hidden" name="action" value="setstatus">';
    print '<select name="new_status">';
    print '<option value="0">'.$langs->trans('PressingStatusReceived').'</option>';
    print '<option value="1">'.$langs->trans('PressingStatusWashing').'</option>';
    print '<option value="2">'.$langs->trans('PressingStatusIroning').'</option>';
    print '<option value="3">'.$langs->trans('PressingStatusReady').'</option>';
    print '<option value="4">'.$langs->trans('PressingStatusDelivered').'</option>';
    print '</select>';
    print '<input type="submit" class="button" value="'.$langs->trans('Update').'">';
    print '</form>';

    print '<h3>'.$langs->trans('LaundryItems').'</h3>';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
    print '<input type="hidden" name="action" value="additem">';
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre"><td>'.$langs->trans('Item').'</td><td>'.$langs->trans('Action').'</td><td>'.$langs->trans('Qty').'</td><td>'.$langs->trans('UnitPrice').'</td><td>'.$langs->trans('TotalHT').'</td></tr>';

    $items = $object->fetchItems();
    foreach ($items as $it) {
        print '<tr class="oddeven"><td>'.dol_escape_htmltag($it->label).'</td><td>'.dol_escape_htmltag($it->service_action).'</td><td>'.$it->qty.'</td><td>'.price($it->unit_price).'</td><td>'.price($it->total_ht).'</td></tr>';
    }

    print '<tr class="liste_titre">';
    print '<td><input type="text" name="item_label" required></td>';
    print '<td><select name="service_action"><option value="washing">'.$langs->trans('Washing').'</option><option value="ironing">'.$langs->trans('Ironing').'</option><option value="drycleaning">'.$langs->trans('DryCleaning').'</option></select></td>';
    print '<td><input type="number" name="qty" min="1" value="1"></td>';
    print '<td><input type="number" step="0.01" name="unit_price" value="0"></td>';
    print '<td><input type="submit" class="button" value="'.$langs->trans('Add').'" ></td>';
    print '</tr>';
    print '</table></form>';

    print dol_get_fiche_end();
}

llxFooter();
$db->close();
