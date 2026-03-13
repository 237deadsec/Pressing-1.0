<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Clothing pricing catalog object.
 */
class PressingPrice extends CommonObject
{
    public $element = 'pressing_price';
    public $table_element = 'pressing_prices';
    public $ismultientitymanaged = 1;

    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'position' => 1, 'notnull' => 1, 'visible' => 0),
        'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'default' => 1),
        'label' => array('type' => 'varchar(128)', 'label' => 'Label', 'enabled' => 1, 'position' => 10, 'notnull' => 1, 'visible' => 1),
        'price_washing' => array('type' => 'double(24,8)', 'label' => 'PriceWashing', 'enabled' => 1, 'position' => 20, 'visible' => 1),
        'price_ironing' => array('type' => 'double(24,8)', 'label' => 'PriceIroning', 'enabled' => 1, 'position' => 30, 'visible' => 1),
        'price_drycleaning' => array('type' => 'double(24,8)', 'label' => 'PriceDryCleaning', 'enabled' => 1, 'position' => 40, 'visible' => 1),
        'active' => array('type' => 'smallint', 'label' => 'Active', 'enabled' => 1, 'position' => 50, 'default' => 1, 'visible' => 1),
    );

    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }
}
