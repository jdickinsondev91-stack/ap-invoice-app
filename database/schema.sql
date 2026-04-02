CREATE TABLE vendors (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE invoice_statuses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL UNIQUE,
    slug        VARCHAR(255) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL,
    sort_order  INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE invoice_status_transitions (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_invoice_status_id  INT UNSIGNED NOT NULL,
    to_invoice_status_id    INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_transition (from_invoice_status_id, to_invoice_status_id),
    FOREIGN KEY (from_invoice_status_id) REFERENCES invoice_statuses(id),
    FOREIGN KEY (to_invoice_status_id)   REFERENCES invoice_statuses(id)
);

CREATE TABLE invoices (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id               INT UNSIGNED NOT NULL,
    invoice_status_id       INT UNSIGNED NOT NULL,
    invoice_number          VARCHAR(100) NOT NULL,
    invoice_date            DATE NOT NULL,
    due_date                DATE NOT NULL,
    amount                  INT NOT NULL,
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_invoice_number_per_vendor (vendor_id, invoice_number),
    FOREIGN KEY (vendor_id)  REFERENCES vendors(id),
    FOREIGN KEY (invoice_status_id)  REFERENCES invoice_statuses(id)
);

CREATE TABLE invoice_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id   INT UNSIGNED NOT NULL,
    description  VARCHAR(255) NOT NULL,
    quantity     DECIMAL(10, 4) NOT NULL,
    unit_price   INT NOT NULL,
    total        INT NOT NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE invoice_status_history (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id          INT UNSIGNED NOT NULL,
    invoice_status_id   INT UNSIGNED NOT NULL,
    changed_by          VARCHAR(100) NOT NULL DEFAULT 'system',
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_status_id)  REFERENCES invoice_statuses(id)
);

CREATE TABLE duplicate_invoice_flags (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id          INT UNSIGNED NOT NULL,
    matched_invoice_id  INT UNSIGNED NOT NULL, 
    reason              VARCHAR(255) NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id)         REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- -----------------------------------------------------
-- Seed data
-- -----------------------------------------------------

INSERT INTO invoice_statuses (id, name, slug, description, sort_order) VALUES
    (1, 'Pending',  'pending',  'Awaiting review and approval', 1),
    (2, 'Approved', 'approved', 'Approved and ready for payment', 2),
    (3, 'Paid',     'paid',     'Payment has been released', 3),
    (4, 'Rejected', 'rejected', 'Invoice has been rejected', 4);

INSERT INTO invoice_status_transitions (from_invoice_status_id, to_invoice_status_id) VALUES
    (1, 2),
    (1, 4),
    (2, 3),
    (2, 4);

INSERT INTO vendors (id, name, email) VALUES
    (1, 'Acme Supplies Ltd',    'billing@acme.example'),
    (2, 'BuildRight Materials', 'accounts@buildright.example'),
    (3, 'TechParts Co',         'invoices@techparts.example');

INSERT INTO invoices (id, vendor_id, invoice_status_id, invoice_number, invoice_date, due_date, amount) VALUES
    (1, 1, 1, 'INV-2024-001', '2024-03-01', '2024-03-31', 150000),
    (2, 1, 2, 'INV-2024-002', '2024-03-05', '2024-04-04', 220000),
    (3, 2, 3, 'INV-2024-003', '2024-02-15', '2024-03-01',  85000),
    (4, 3, 4, 'INV-2024-004', '2024-03-10', '2024-04-09',  47500);

INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES
    (1, 'Industrial bolts (box of 100)', 10.0000,  5000,  50000),
    (1, 'Steel brackets',                 5.0000, 20000, 100000),
    (2, 'Copper pipe 3m',                 8.0000, 15000, 120000),
    (2, 'Pipe fittings',                 20.0000,  5000, 100000),
    (3, 'Timber 4x2 (3.6m)',             10.0000,  8500,  85000),
    (4, 'Circuit board v2',               2.5000, 19000,  47500);

INSERT INTO invoice_status_history (invoice_id, invoice_status_id, changed_by) VALUES
    (1, 1, 'system'),
    (2, 1, 'system'),
    (2, 2, 'jane.smith'),
    (3, 1, 'system'),
    (3, 2, 'jane.smith'),
    (3, 3, 'john.doe'),
    (4, 1, 'system'),
    (4, 4, 'jane.smith');