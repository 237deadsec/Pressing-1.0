<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once __DIR__.'/../class/pressingorder.class.php';
require_once __DIR__.'/../core/modules/pressing/doc/pdf_ticket.modules.php';

$langs->loadLangs(array('pressing@pressing', 'main'));

$id = GETPOSTINT('id');
if (!$user->rights->pressing->read) {
    accessforbidden();
}

$object = new PressingOrder($db);
$object->fetch($id);

$model = new pdf_ticket($db);
$file = $model->write_file($object, $langs);

if ($file) {
    header('Location: '.DOL_URL_ROOT.'/document.php?modulepart=pressing&file='.urlencode(basename(dirname($file)).'/'.basename($file)));
    exit;
}

setEventMessages($model->error, $model->errors, 'errors');
header('Location: '.DOL_URL_ROOT.'/custom/pressing/order/card.php?id='.$id);
exit;
