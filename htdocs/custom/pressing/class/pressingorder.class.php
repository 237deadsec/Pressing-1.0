<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Pressing order object.
 */
class PressingOrder extends CommonObject
{
    public $element = 'pressing_order';
    public $table_element = 'pressing_order';
    public $ismultientitymanaged = 1;

    const STATUS_RECEIVED = 0;
    const STATUS_WASHING = 1;
    const STATUS_IRONING = 2;
    const STATUS_READY = 3;
    const STATUS_DELIVERED = 4;

    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0),
        'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'default' => 1),
        'ref' => array('type' => 'varchar(128)', 'label' => 'TicketNumber', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1),
        'fk_soc' => array('type' => 'integer', 'label' => 'Customer', 'enabled' => 1, 'position' => 20, 'notnull' => 1, 'visible' => 1),
        'date_deposit' => array('type' => 'datetime', 'label' => 'DepositDate', 'enabled' => 1, 'position' => 30, 'notnull' => 1, 'visible' => 1),
        'date_expected_pickup' => array('type' => 'datetime', 'label' => 'ExpectedPickupDate', 'enabled' => 1, 'position' => 40, 'visible' => 1),
        'service_type' => array('type' => 'varchar(32)', 'label' => 'ServiceType', 'enabled' => 1, 'position' => 50, 'visible' => 1),
        'weight_kg' => array('type' => 'double(24,8)', 'label' => 'WeightKg', 'enabled' => 1, 'position' => 60, 'visible' => 1),
        'price_per_kg' => array('type' => 'double(24,8)', 'label' => 'PricePerKg', 'enabled' => 1, 'position' => 70, 'visible' => 1),
        'total_ht' => array('type' => 'double(24,8)', 'label' => 'TotalHT', 'enabled' => 1, 'position' => 80, 'visible' => 1),
        'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'position' => 90, 'default' => 0, 'visible' => 1),
        'note_private' => array('type' => 'text', 'label' => 'Note', 'enabled' => 1, 'position' => 100, 'visible' => 1),
        'fk_facture' => array('type' => 'integer', 'label' => 'Invoice', 'enabled' => 1, 'position' => 110, 'visible' => 1),
        'amount_paid' => array('type' => 'double(24,8)', 'label' => 'AmountPaid', 'enabled' => 1, 'position' => 120, 'visible' => 1),
        'payment_mode' => array('type' => 'varchar(16)', 'label' => 'PaymentMode', 'enabled' => 1, 'position' => 130, 'visible' => 1),
        'datec' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1),
        'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1),
    );

    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    public function create(User $user, $notrigger = false)
    {
        if (empty($this->ref) || $this->ref === '(PROV)') {
            $this->ref = $this->getNextNumRef();
        }

        if ($this->service_type === 'weight') {
            $this->total_ht = price2num((float) $this->weight_kg * (float) $this->price_per_kg);
        }

        return $this->createCommon($user, $notrigger);
    }

    public function getNextNumRef()
    {
        global $conf;

        $prefix = 'PR-'.dol_print_date(dol_now(), '%Y%m%d').'-';
        $sql = "SELECT MAX(CAST(SUBSTRING(ref, ".((int) strlen($prefix) + 1).") AS UNSIGNED)) as maxref";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE ref LIKE '".$this->db->escape($prefix)."%'";
        $sql .= " AND entity = ".((int) $conf->entity);

        $resql = $this->db->query($sql);
        $num = 1;
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $num = ((int) $obj->maxref) + 1;
        }

        return $prefix.sprintf('%04d', $num);
    }

    public function fetchItems()
    {
        require_once __DIR__.'/pressingitem.class.php';
        $items = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."pressing_items WHERE fk_order = ".((int) $this->id);
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $item = new PressingItem($this->db);
                $item->fetch($obj->rowid);
                $items[] = $item;
            }
        }

        return $items;
    }

    public function getStatusLabel()
    {
        global $langs;
        $labels = array(
            self::STATUS_RECEIVED => $langs->trans('PressingStatusReceived'),
            self::STATUS_WASHING => $langs->trans('PressingStatusWashing'),
            self::STATUS_IRONING => $langs->trans('PressingStatusIroning'),
            self::STATUS_READY => $langs->trans('PressingStatusReady'),
            self::STATUS_DELIVERED => $langs->trans('PressingStatusDelivered'),
        );

        return isset($labels[$this->status]) ? $labels[$this->status] : $langs->trans('Unknown');
    }
}
