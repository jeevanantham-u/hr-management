INSERT INTO
    `role_permissions` (`role_id`, `permission_id`)
VALUES
    -- Super Admin (All Permissions: 1 through 8)
    (1, 1),
    (1, 2),
    (1, 3),
    (1, 4),
    (1, 5),
    (1, 6),
    (1, 7),
    (1, 8),
    -- General Manager (Reports, Reservations, Check-ins, Orders, Payments)
    (2, 2),
    (2, 3),
    (2, 4),
    (2, 7),
    (2, 8),
    -- Department Head (Reports, Housekeeping, Maintenance, Orders, Payments)
    (3, 2),
    (3, 5),
    (3, 6),
    (3, 7),
    (3, 8),
    -- Supervisor (Reservations, Check-ins, Housekeeping, Maintenance, Orders)
    (4, 3),
    (4, 4),
    (4, 5),
    (4, 6),
    (4, 7),
    -- Front Desk Agent (Reservations, Check-ins, Payments)
    (5, 3),
    (5, 4),
    (5, 8),
    -- Service Staff (Housekeeping tasks, Maintenance tasks, Orders)
    (6, 5),
    (6, 6),
    (6, 7);