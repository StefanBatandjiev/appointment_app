<?php

return [

    'column_toggle' => [

        'heading' => 'Колони',

    ],

    'columns' => [

        'actions' => [
            'label' => 'Акција|Акции',
        ],

        'text' => [

            'actions' => [
                'collapse_list' => 'Прикажи :count помалку',
                'expand_list' => 'Прикажи :count повеќе',
            ],

            'more_list_items' => 'и :count повеќе',

        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'Избери/одбери сите ставки за групни акции.',
        ],

        'bulk_select_record' => [
            'label' => 'Избери/одбери ставка :key за групни акции.',
        ],

        'bulk_select_group' => [
            'label' => 'Избери/одбери група :title за групни акции.',
        ],

        'search' => [
            'label' => 'Пребарај',
            'placeholder' => 'Пребарај',
            'indicator' => 'Пребарај',
        ],

    ],

    'summary' => [

        'heading' => 'Резиме',

        'subheadings' => [
            'all' => 'Сите :label',
            'group' => 'Резиме на :group',
            'page' => 'Оваа страница',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'Просек',
            ],

            'count' => [
                'label' => 'Број',
            ],

            'sum' => [
                'label' => 'Збир',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'Заврши со пренаредување на записи',
        ],

        'enable_reordering' => [
            'label' => 'Пренареди записи',
        ],

        'filter' => [
            'label' => 'Филтер',
        ],

        'group' => [
            'label' => 'Групирај',
        ],

        'open_bulk_actions' => [
            'label' => 'Групни акции',
        ],

        'toggle_columns' => [
            'label' => 'Пребарувај колони',
        ],

    ],

    'empty' => [

        'heading' => 'Нема :model',

        'description' => 'Креирај :model за да започнеш.',

    ],

    'filters' => [

        'actions' => [

            'apply' => [
                'label' => 'Примени филтри',
            ],

            'remove' => [
                'label' => 'Отстрани филтер',
            ],

            'remove_all' => [
                'label' => 'Отстрани сите филтри',
                'tooltip' => 'Отстрани сите филтри',
            ],

            'reset' => [
                'label' => 'Ресетирај',
            ],

        ],

        'heading' => 'Филтри',

        'indicator' => 'Активни филтри',

        'multi_select' => [
            'placeholder' => 'Сите',
        ],

        'select' => [
            'placeholder' => 'Сите',
        ],

        'trashed' => [

            'label' => 'Избришани записи',

            'only_trashed' => 'Само избришани записи',

            'with_trashed' => 'Со избришани записи',

            'without_trashed' => 'Без избришани записи',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'Групирај по',
                'placeholder' => 'Групирај по',
            ],

            'direction' => [

                'label' => 'Правец на групирање',

                'options' => [
                    'asc' => 'Нарасно',
                    'desc' => 'Надолно',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'Влечи и испушти ги записите во редослед.',

    'selection_indicator' => [

        'selected_count' => '1 запис избран|:count записи избрани',

        'actions' => [

            'select_all' => [
                'label' => 'Избери сите :count',
            ],

            'deselect_all' => [
                'label' => 'Не избирај ништо',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'Сортирај по',
            ],

            'direction' => [

                'label' => 'Правец на сортирање',

                'options' => [
                    'asc' => 'Нарасно',
                    'desc' => 'Надолно',
                ],

            ],

        ],

    ],

];
