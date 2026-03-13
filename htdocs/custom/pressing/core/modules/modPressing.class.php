<?php
/* Copyright */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module Pressing.
 */
class modPressing extends DolibarrModules
{
    /**
     * Constructor.
     *
     * @param DoliDB $db Database handler.
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 106500;
        $this->rights_class = 'pressing';

        $this->family = 'services';
        $this->module_position = '80';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'Laundry and dry-cleaning operations manager';
        $this->descriptionlong = 'Manage intake, workflow, pricing, ticket printing and invoices for pressing activities.';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'generic';

        $this->module_parts = array(
            'css' => array('/pressing/css/pressing.css.php'),
            'triggers' => 0,
            'models' => 1,
            'hooks' => array('invoicecard', 'thirdpartycard', 'takepos'),
        );

        $this->dirs = array('/pressing/temp');
        $this->config_page_url = array('setup.php@pressing');
        $this->hidden = false;
        $this->depends = array('modSociete', 'modFacture', 'modProduct');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(8, 0);
        $this->langfiles = array('pressing@pressing');
        $this->const = array(
            0 => array('PRESSING_PRICE_PER_KG', 'chaine', '0', 'Default price per kg', 0, 'current'),
            1 => array('PRESSING_DEFAULT_PAYMENTMODE', 'chaine', 'LIQ', 'Default payment code', 0, 'current'),
        );

        if (!isset($conf->pressing) || !isset($conf->pressing->enabled)) {
            $conf->pressing = new stdClass();
            $conf->pressing->enabled = 0;
        }

        $this->tabs = array();

        $this->dictionaries = array(
            'langs' => 'pressing@pressing',
            'tabname' => array('c_pressing_prices'),
            'tablib' => array('PressingPriceCatalog'),
            'tabsql' => array('SELECT rowid, label, price_washing, price_ironing, price_drycleaning FROM '.MAIN_DB_PREFIX.'pressing_prices'),
            'tabsqlsort' => array('label ASC'),
            'tabfield' => array('label,price_washing,price_ironing,price_drycleaning'),
            'tabfieldvalue' => array('label,price_washing,price_ironing,price_drycleaning'),
            'tabfieldinsert' => array('label,price_washing,price_ironing,price_drycleaning'),
            'tabrowid' => array('rowid'),
            'tabcond' => array($conf->pressing->enabled),
        );

        $this->rights = array();
        $r = 0;
        $this->rights[$r][0] = 106501;
        $this->rights[$r][1] = 'Read pressing orders';
        $this->rights[$r][4] = 'read';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = 106502;
        $this->rights[$r][1] = 'Create or modify pressing orders';
        $this->rights[$r][4] = 'write';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = 106503;
        $this->rights[$r][1] = 'Delete pressing orders';
        $this->rights[$r][4] = 'delete';
        $this->rights[$r][5] = '';

        $r++;
        $this->rights[$r][0] = 106504;
        $this->rights[$r][1] = 'Manage pressing settings and prices';
        $this->rights[$r][4] = 'setup';
        $this->rights[$r][5] = '';

        $this->menu = array();
        $r = 0;
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=products',
            'type' => 'left',
            'titre' => 'PressingManager',
            'mainmenu' => 'products',
            'leftmenu' => 'pressing',
            'url' => '/custom/pressing/index.php',
            'langs' => 'pressing@pressing',
            'position' => 900,
            'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->read',
            'target' => '',
            'user' => 2,
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=products,fk_leftmenu=pressing',
            'type' => 'left',
            'titre' => 'Orders',
            'mainmenu' => 'products',
            'leftmenu' => 'pressing_orders',
            'url' => '/custom/pressing/order/list.php',
            'langs' => 'pressing@pressing',
            'position' => 10,
            'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->read',
            'target' => '',
            'user' => 2,
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=products,fk_leftmenu=pressing',
            'type' => 'left',
            'titre' => 'NewOrder',
            'mainmenu' => 'products',
            'leftmenu' => 'pressing_new_order',
            'url' => '/custom/pressing/order/card.php?action=create',
            'langs' => 'pressing@pressing',
            'position' => 20,
            'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->write',
            'target' => '',
            'user' => 2,
        );

        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=home',
            'type' => 'top',
            'titre' => 'PressingManager',
            'mainmenu' => 'pressing',
            'leftmenu' => '',
            'url' => '/custom/pressing/index.php',
            'langs' => 'pressing@pressing',
            'position' => 1000,
            'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->read',
            'target' => '',
            'user' => 2,
        );
    }

    /**
     * Init module.
     */
    public function init($options = '')
    {
        $sql = array();
        $this->_load_tables('/pressing/sql/');

        return $this->_init($sql, $options);
    }

    /**
     * Remove module.
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}
