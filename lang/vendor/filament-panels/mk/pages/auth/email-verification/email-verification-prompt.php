<?php

return [

    'title' => 'Потврдете ја вашата е-пошта',

    'heading' => 'Потврдете ја вашата е-пошта',

    'actions' => [

        'resend_notification' => [
            'label' => 'Испратете повторно',
        ],

    ],

    'messages' => [
        'notification_not_received' => 'Не ја добивте е-поштата што ја испративме?',
        'notification_sent' => 'Испративме е-пошта на :email со упатства за потврда на вашата е-пошта.',
    ],

    'notifications' => [

        'notification_resent' => [
            'title' => 'Повторно ја испративме е-поштата.',
        ],

        'notification_resend_throttled' => [
            'title' => 'Премногу обиди за повторно испраќање',
            'body' => 'Обидете се повторно за :seconds секунди.',
        ],

    ],

];
