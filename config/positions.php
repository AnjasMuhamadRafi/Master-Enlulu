<?php

/**
 * Position Management Configuration
 * 
 * Mapping antara ADMIN/PIC Department dengan posisi yang mereka kelola
 */

return [
    /**
     * Mapping ADMIN/PIC ke posisi yang dikelola
     * Format: 'DEPARTMENT' => ['posisi1', 'posisi2', ...]
     */
    'admin_pic_departments' => [
        'SPRINTER' => [
            'SPRINTER',
            'KURIR MOTOR',
        ],
        'TRANSPORTER' => [
            'TRANSPORTER',
            'DRIVER',
        ],
        'WH' => [
            'WH',
            'QC',
            'SPRINTER PICKUP',
            'CALL CENTER',
            'ADMIN',
            'PIC',
            'ADMIN-BACKUP',
        ],
    ],

    /**
     * List semua posisi operasional (non-ADMIN/PIC)
     */
    'operational_positions' => [
        'SPRINTER',
        'KURIR MOTOR',
        'TRANSPORTER',
        'DRIVER',
        'WH',
        'QC',
        'SPRINTER PICKUP',
        'CALL CENTER',
        'ADMIN',
        'PIC',
        'ADMIN-BACKUP',
    ],

    /**
     * List semua ADMIN/PIC roles
     */
    'admin_pic_roles' => [
        'ADMIN/PIC-SPRINTER',
        'ADMIN/PIC-TRANSPORTER',
        'ADMIN/PIC-WH',
    ],
];
