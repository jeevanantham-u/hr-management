INSERT INTO
    `leave_types` (
        `id`,
        `name`,
        `description`,
        `max_days`,
        `created_at`,
        `updated_at`
    )
VALUES
    (
        1,
        'Casual Leave',
        'Casual leave for personal reasons',
        12,
        NOW(),
        NOW()
    ),
    (
        2,
        'Sick Leave',
        'Leave due to illness',
        12,
        NOW(),
        NOW()
    ),
    (
        3,
        'Earned Leave',
        'Earned leave as per policy',
        20,
        NOW(),
        NOW()
    ),
    (
        4,
        'Maternity Leave',
        'Leave for maternity purposes',
        180,
        NOW(),
        NOW()
    ),
    (
        5,
        'Compensatory Off',
        'Leave credited for working on full holidays or extra shifts',
        0,
        NOW(),
        NOW()
    ),
    (
        6,
        'Paternity Leave',
        'Leave granted to male employees upon the birth of a child',
        15,
        NOW(),
        NOW()
    );