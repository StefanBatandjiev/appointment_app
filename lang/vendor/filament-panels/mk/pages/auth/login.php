<?php

return [

    'title' => 'Најава',

    'heading' => 'Најави се',

    'actions' => [

        'register' => [
            'before' => 'или',
            'label' => 'регистрирајте се за сметка',
        ],

        'request_password_reset' => [
            'label' => 'Заборавена лозинка?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Адреса на електронска пошта',
        ],

        'password' => [
            'label' => 'Лозинка',
        ],

        'remember' => [
            'label' => 'Запомни ме',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Најави се',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Овие акредитиви не одговараат на нашите записи.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Премногу обиди за најава',
            'body' => 'Ве молиме обидете се повторно за :seconds секунди.',
        ],

    ],

];
