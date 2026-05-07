<?php

return [

    /* ── Navigation & Auth ── */
    'login'           => 'Login',
    'logout'          => 'Logout',
    'register'        => 'Register',
    'profile'         => 'Profile',
    'settings'        => 'Settings',
    'language'        => 'Language',

    /* ── Order statuses ── */
    'status_pending'       => 'Pending',
    'status_confirmed'     => 'Confirmed',
    'status_delivering'    => 'Out for delivery',
    'status_delivered'     => 'Delivered',
    'status_cancelled'     => 'Cancelled',
    'status_processing'    => 'Processing',

    /* ── Dashboard KPI ── */
    'net_revenue'          => 'Net revenue',
    'orders_this_month'    => 'Orders this month',
    'avg_basket'           => 'Average basket',
    'delivery_rate'        => 'Delivery rate',
    'today_net_revenue'    => 'Net revenue today',
    'today_orders'         => 'Orders today',
    'orders_received'      => 'orders received',
    'currency_per_order'   => ':devise / order',
    'vs_last_month'        => '% vs last month',
    'vs_yesterday'         => '% vs yesterday',
    'today'                => 'today',
    'stable_today'         => '→ Stable today',
    'same_as_yesterday'    => '— Same as yesterday',
    'excellent'            => '✓ Excellent',
    'to_improve'           => '⚠ Needs improvement',

    /* ── Charts ── */
    'revenue_7days'        => 'Revenue — Last 7 days',
    'orders_chart'         => 'Orders',
    'last_7_days'          => 'Last 7 days',
    'last_30_days'         => 'Last 30 days',
    'best_day'             => 'Best day',
    'avg_per_day'          => 'Avg. / day',

    /* ── Days ── */
    'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],

    /* ── Months ── */
    'months' => [
        '', 'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ],

    /* ── Kanban ── */
    'kanban_pending'       => 'Pending',
    'kanban_confirmed'     => 'Confirmed',
    'kanban_delivering'    => 'Out for delivery',
    'kanban_done'          => 'Completed',
    'kanban_cancelled'     => 'Cancelled',
    'this_month'           => 'this month',

    /* ── Orders (table) ── */
    'orders'               => 'Orders',
    'order'                => 'Order',
    'order_ref'            => 'Ref / Client',
    'status'               => 'Status',
    'amount'               => 'Amount',
    'assignment'           => 'Assignment',
    'address'              => 'Address',
    'product'              => 'Product',
    'date'                 => 'Date',
    'actions'              => 'Actions',
    'assigned'             => '✔ Assigned',
    'not_assigned'         => 'Not assigned',
    'assign'               => 'Assign',
    'cancel'               => 'Cancel',
    'restore'              => 'Restore',
    'confirm'              => 'Confirm',
    'deliver'              => 'Mark delivered',
    'send_to_company'      => 'Send to company',
    'search_placeholder'   => 'Search client or #ID…',
    'filter_all_dates'     => 'All dates',
    'filter_today'         => 'Today',
    'filter_week'          => 'This week',
    'filter_month'         => 'This month',
    'filter_custom'        => 'Custom',
    'all_statuses'         => 'All statuses',
    'no_orders'            => 'No orders found.',
    'delivery_fee'         => 'Delivery fee',
    'delivery_zone'        => 'Delivery zone',
    'destination'          => 'Destination',
    'client'               => 'Client',
    'driver'               => 'Driver',
    'company'              => 'Company',

    /* ── Quick actions ── */
    'quick_actions'        => 'Quick actions',
    'direct_access'        => 'Direct access to common tasks',
    'see_manage'           => 'View & manage',
    'new_product'          => 'New product',
    'add_to_catalog'       => 'Add to catalog',
    'drivers'              => 'Drivers',
    'see_online'           => 'View online',
    'payments'             => 'Payments',
    'revenues_received'    => 'Revenues received',

    /* ── Commissions ── */
    'gross_revenue'        => 'Gross revenue',
    'commissions_paid'     => 'Commissions paid',
    'net_of_commissions'   => 'Net of commissions',

    /* ── General ── */
    'save'                 => 'Save',
    'close'                => 'Close',
    'send'                 => 'Send',
    'loading'              => 'Loading…',
    'success'              => 'Success',
    'error'                => 'Error',
    'warning'              => 'Warning',
    'see_all'              => 'See all →',
    'recent_orders'        => 'Recent orders',
    'per_page'             => 'per page',
    'previous'             => 'Previous',
    'next'                 => 'Next',
];
