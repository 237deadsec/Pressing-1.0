<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Pressing order item object.
 */
class PressingItem extends CommonObject
{
    public $element = 'pressing_item';
    public $table_element = 'pressing_items';
    public $ismultientitymanaged = 1;

    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0),
        'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'default' => 1),
        'fk_order' => array('type' => 'integer', 'label' => 'Order', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1),
        'fk_price' => array('type' => 'integer', 'label' => 'PriceItem', 'enabled' => 1, 'position' => 20, 'visible' => 1),
        'label' => array('type' => 'varchar(128)', 'label' => 'Label', 'enabled' => 1, 'position' => 30, 'visible' => 1),
        'service_action' => array('type' => 'varchar(32)', 'label' => 'Action', 'enabled' => 1, 'position' => 40, 'visible' => 1),
        'qty' => array('type' => 'integer', 'label' => 'Qty', 'enabled' => 1, 'position' => 50, 'default' => 1, 'visible' => 1),
        'unit_price' => array('type' => 'double(24,8)', 'label' => 'UnitPrice', 'enabled' => 1, 'position' => 60, 'visible' => 1),
        'total_ht' => array('type' => 'double(24,8)', 'label' => 'TotalHT', 'enabled' => 1, 'position' => 70, 'visible' => 1),
        'datec' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1),
        'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1),
    );

    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    public function create(User $user, $notrigger = false)
    {
        $this->total_ht = price2num((float) $this->qty * (float) $this->unit_price);
        return $this->createCommon($user, $notrigger);
    }
}
