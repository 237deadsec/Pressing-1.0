<?php
require_once DOL_DOCUMENT_ROOT.'/core/modules/ModelePDFFactures.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once __DIR__.'/../../../../class/pressingorder.class.php';

/**
 * A5 ticket pdf model.
 */
class pdf_ticket extends ModelePDFFactures
{
    public $name = 'ticket';
    public $description = 'A5 pressing receipt model';
    public $type = 'pdf';
    public $version = 'dolibarr';

    public function __construct($db)
    {
        global $langs;
        $this->db = $db;
        $langs->load('pressing@pressing');
    }

    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
        global $conf;

        if (!is_object($outputlangs)) {
            $outputlangs = $langs;
        }

        $dir = $conf->pressing->multidir_output[$object->entity].'/'.$object->ref;
        if (!dol_mkdir($dir)) {
            $this->error = 'ErrorFailedToCreateDir';
            return 0;
        }

        $file = $dir.'/'.$object->ref.'_ticket.pdf';
        $pdf = pdf_getInstance(array('format' => 'A5'));
        if (class_exists('TCPDF')) {
            $pdf->SetFont(pdf_getPDFFont($outputlangs), '', 10);
        }
        $pdf->SetTitle($object->ref);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $posx = 10;
        $posy = 10;

        $pdf->SetFont('', 'B', 14);
        $pdf->SetXY($posx, $posy);
        $pdf->MultiCell(120, 6, $outputlangs->trans('LaundryTicket'));

        $pdf->SetFont('', '', 10);
        $posy += 10;
        $pdf->SetXY($posx, $posy);
        $pdf->MultiCell(120, 5, $outputlangs->trans('TicketNumber').': '.$object->ref);

        $posy += 6;
        $pdf->SetXY($posx, $posy);
        $pdf->MultiCell(120, 5, $outputlangs->trans('DepositDate').': '.dol_print_date($object->date_deposit, 'day'));

        $posy += 6;
        $pdf->SetXY($posx, $posy);
        $pdf->MultiCell(120, 5, $outputlangs->trans('ExpectedPickupDate').': '.dol_print_date($object->date_expected_pickup, 'day'));

        $posy += 8;
        $pdf->SetFont('', 'B', 10);
        $pdf->SetXY($posx, $posy);
        $pdf->Cell(70, 6, $outputlangs->trans('Item'));
        $pdf->Cell(20, 6, $outputlangs->trans('Qty'));
        $pdf->Cell(25, 6, $outputlangs->trans('TotalHT'));

        $pdf->SetFont('', '', 10);
        $posy += 7;
        $items = $object->fetchItems();
        foreach ($items as $item) {
            $pdf->SetXY($posx, $posy);
            $pdf->Cell(70, 5, $item->label.' / '.$item->service_action);
            $pdf->Cell(20, 5, $item->qty, 0, 0, 'R');
            $pdf->Cell(25, 5, price($item->total_ht), 0, 0, 'R');
            $posy += 5;
        }

        if ($object->service_type === 'weight' && $object->weight_kg > 0) {
            $pdf->SetXY($posx, $posy + 2);
            $pdf->Cell(70, 5, $outputlangs->trans('LaundryByWeight').' '.$object->weight_kg.'kg');
            $pdf->Cell(20, 5, '1', 0, 0, 'R');
            $pdf->Cell(25, 5, price($object->weight_kg * $object->price_per_kg), 0, 0, 'R');
            $posy += 7;
        }

        $pdf->SetFont('', 'B', 11);
        $pdf->SetXY($posx, $posy + 5);
        $pdf->Cell(90, 6, $outputlangs->trans('TotalHT').': '.price($object->total_ht), 0, 0, 'R');

        $pdf->SetFont('', '', 9);
        $pdf->SetXY($posx, $posy + 15);
        $pdf->MultiCell(120, 4, $outputlangs->trans('PressingTicketFooter'));

        $pdf->Output($file, 'F');

        return $file;
    }
}
