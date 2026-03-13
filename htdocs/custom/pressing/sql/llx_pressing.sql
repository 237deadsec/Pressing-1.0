-- Copyright

CREATE TABLE llx_pressing_order (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity integer NOT NULL DEFAULT 1,
    ref varchar(128) NOT NULL,
    fk_soc integer NOT NULL,
    date_deposit datetime NOT NULL,
    date_expected_pickup datetime NULL,
    service_type varchar(32) NOT NULL,
    weight_kg double(24,8) DEFAULT 0,
    price_per_kg double(24,8) DEFAULT 0,
    total_ht double(24,8) DEFAULT 0,
    status smallint NOT NULL DEFAULT 0,
    note_private text,
    fk_facture integer NULL,
    amount_paid double(24,8) DEFAULT 0,
    payment_mode varchar(16) NULL,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer NULL,
    fk_user_modif integer NULL,
    import_key varchar(14),
    model_pdf varchar(255),
    INDEX idx_pressing_order_ref (ref),
    INDEX idx_pressing_order_soc (fk_soc),
    INDEX idx_pressing_order_status (status)
) ENGINE=innodb;

CREATE TABLE llx_pressing_items (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity integer NOT NULL DEFAULT 1,
    fk_order integer NOT NULL,
    fk_price integer NULL,
    label varchar(128) NOT NULL,
    service_action varchar(32) NOT NULL,
    qty integer NOT NULL DEFAULT 1,
    unit_price double(24,8) DEFAULT 0,
    total_ht double(24,8) DEFAULT 0,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer NULL,
    fk_user_modif integer NULL,
    INDEX idx_pressing_items_order (fk_order)
) ENGINE=innodb;

CREATE TABLE llx_pressing_prices (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    entity integer NOT NULL DEFAULT 1,
    label varchar(128) NOT NULL,
    price_washing double(24,8) DEFAULT 0,
    price_ironing double(24,8) DEFAULT 0,
    price_drycleaning double(24,8) DEFAULT 0,
    active smallint DEFAULT 1,
    datec datetime NOT NULL,
    tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_creat integer NULL,
    fk_user_modif integer NULL,
    UNIQUE uk_pressing_price_label (entity, label)
) ENGINE=innodb;

CREATE TABLE llx_pressing_status (
    rowid integer AUTO_INCREMENT PRIMARY KEY,
    code varchar(32) NOT NULL,
    label varchar(128) NOT NULL,
    position integer NOT NULL DEFAULT 0,
    active smallint DEFAULT 1,
    UNIQUE uk_pressing_status_code (code)
) ENGINE=innodb;

INSERT INTO llx_pressing_status(code, label, position) VALUES
('RECEIVED', 'Received', 10),
('WASHING', 'Washing', 20),
('IRONING', 'Ironing', 30),
('READY', 'Ready', 40),
('DELIVERED', 'Delivered', 50);

INSERT INTO llx_pressing_prices(entity, label, price_washing, price_ironing, price_drycleaning, datec) VALUES
(1, 'Shirt', 2.50000000, 1.00000000, 3.50000000, NOW()),
(1, 'Pants', 3.00000000, 1.20000000, 4.00000000, NOW()),
(1, 'Dress', 4.00000000, 2.00000000, 5.00000000, NOW()),
(1, 'Suit', 6.50000000, 3.00000000, 8.00000000, NOW()),
(1, 'Jacket', 4.50000000, 2.20000000, 6.00000000, NOW()),
(1, 'Traditional dress', 5.00000000, 2.50000000, 6.50000000, NOW());
