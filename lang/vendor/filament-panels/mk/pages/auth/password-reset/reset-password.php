<?php

return [

    'title' => 'Ресетирај ја лозинката',

    'heading' => 'Ресетирај ја лозинката',

    'form' => [

        'email' => [
            'label' => 'Електронска пошта',
        ],

        'password' => [
            'label' => 'Лозинка',
            'validation_attribute' => 'лозинка',
        ],

        'password_confirmation' => [
            'label' => 'Потврди лозинка',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Ресетирај лозинка',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Премногу обиди за ресетирање',
            'body' => 'Обидете се повторно за :seconds секунди.',
        ],

    ],

];
