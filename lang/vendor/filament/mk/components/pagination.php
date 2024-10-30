<?php

return [

    'label' => 'Навигација за пагинација',

    'overview' => '{1} Прикажано 1 резултат|[2,*] Прикажано од :first до :last од :total резултати',

    'fields' => [

        'records_per_page' => [

            'label' => 'По страница',

            'options' => [
                'all' => 'Сите',
            ],

        ],

    ],

    'actions' => [

        'first' => [
            'label' => 'Прва',
        ],

        'go_to_page' => [
            'label' => 'Оди на страница :page',
        ],

        'last' => [
            'label' => 'Последна',
        ],

        'next' => [
            'label' => 'Следна',
        ],

        'previous' => [
            'label' => 'Претходна',
        ],

    ],

];
