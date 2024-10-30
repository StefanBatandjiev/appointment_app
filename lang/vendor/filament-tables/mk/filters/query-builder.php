<?php

return [

    'label' => 'Градеж на упити',

    'form' => [

        'operator' => [
            'label' => 'Оператор',
        ],

        'or_groups' => [

            'label' => 'Групи',

            'block' => [
                'label' => 'Дизјункција (ИЛИ)',
                'or' => 'ИЛИ',
            ],

        ],

        'rules' => [

            'label' => 'Правила',

            'item' => [
                'and' => 'И',
            ],

        ],

    ],

    'no_rules' => '(Нема правила)',

    'item_separators' => [
        'and' => 'И',
        'or' => 'ИЛИ',
    ],

    'operators' => [

        'is_filled' => [

            'label' => [
                'direct' => 'Е полнет',
                'inverse' => 'Е празен',
            ],

            'summary' => [
                'direct' => ':attribute е полнет',
                'inverse' => ':attribute е празен',
            ],

        ],

        'boolean' => [

            'is_true' => [

                'label' => [
                    'direct' => 'Е вистинит',
                    'inverse' => 'Не е вистинит',
                ],

                'summary' => [
                    'direct' => ':attribute е вистинит',
                    'inverse' => ':attribute не е вистинит',
                ],

            ],

        ],

        'date' => [

            'is_after' => [

                'label' => [
                    'direct' => 'Е по',
                    'inverse' => 'Не е по',
                ],

                'summary' => [
                    'direct' => ':attribute е по :date',
                    'inverse' => ':attribute не е по :date',
                ],

            ],

            'is_before' => [

                'label' => [
                    'direct' => 'Е пред',
                    'inverse' => 'Не е пред',
                ],

                'summary' => [
                    'direct' => ':attribute е пред :date',
                    'inverse' => ':attribute не е пред :date',
                ],

            ],

            'is_date' => [

                'label' => [
                    'direct' => 'Е дата',
                    'inverse' => 'Не е дата',
                ],

                'summary' => [
                    'direct' => ':attribute е :date',
                    'inverse' => ':attribute не е :date',
                ],

            ],

            'is_month' => [

                'label' => [
                    'direct' => 'Е месец',
                    'inverse' => 'Не е месец',
                ],

                'summary' => [
                    'direct' => ':attribute е :month',
                    'inverse' => ':attribute не е :month',
                ],

            ],

            'is_year' => [

                'label' => [
                    'direct' => 'Е година',
                    'inverse' => 'Не е година',
                ],

                'summary' => [
                    'direct' => ':attribute е :year',
                    'inverse' => ':attribute не е :year',
                ],

            ],

            'form' => [

                'date' => [
                    'label' => 'Датум',
                ],

                'month' => [
                    'label' => 'Месец',
                ],

                'year' => [
                    'label' => 'Година',
                ],

            ],

        ],

        'number' => [

            'equals' => [

                'label' => [
                    'direct' => 'Е еднаков',
                    'inverse' => 'Не е еднаков',
                ],

                'summary' => [
                    'direct' => ':attribute е еднаков на :number',
                    'inverse' => ':attribute не е еднаков на :number',
                ],

            ],

            'is_max' => [

                'label' => [
                    'direct' => 'Е максимален',
                    'inverse' => 'Е поголем од',
                ],

                'summary' => [
                    'direct' => ':attribute е максимален :number',
                    'inverse' => ':attribute е поголем од :number',
                ],

            ],

            'is_min' => [

                'label' => [
                    'direct' => 'Е минимален',
                    'inverse' => 'Е помал од',
                ],

                'summary' => [
                    'direct' => ':attribute е минимален :number',
                    'inverse' => ':attribute е помал од :number',
                ],

            ],

            'aggregates' => [

                'average' => [
                    'label' => 'Просек',
                    'summary' => 'Просек на :attribute',
                ],

                'max' => [
                    'label' => 'Максимум',
                    'summary' => 'Максимум на :attribute',
                ],

                'min' => [
                    'label' => 'Минимум',
                    'summary' => 'Минимум на :attribute',
                ],

                'sum' => [
                    'label' => 'Збир',
                    'summary' => 'Збир на :attribute',
                ],

            ],

            'form' => [

                'aggregate' => [
                    'label' => 'Агреѓат',
                ],

                'number' => [
                    'label' => 'Број',
                ],

            ],

        ],

        'relationship' => [

            'equals' => [

                'label' => [
                    'direct' => 'Има',
                    'inverse' => 'Не има',
                ],

                'summary' => [
                    'direct' => 'Има :count :relationship',
                    'inverse' => 'Не има :count :relationship',
                ],

            ],

            'has_max' => [

                'label' => [
                    'direct' => 'Има максимум',
                    'inverse' => 'Има повеќе од',
                ],

                'summary' => [
                    'direct' => 'Има максимум :count :relationship',
                    'inverse' => 'Има повеќе од :count :relationship',
                ],

            ],

            'has_min' => [

                'label' => [
                    'direct' => 'Има минимум',
                    'inverse' => 'Има помалку од',
                ],

                'summary' => [
                    'direct' => 'Има минимум :count :relationship',
                    'inverse' => 'Има помалку од :count :relationship',
                ],

            ],

            'is_empty' => [

                'label' => [
                    'direct' => 'Е празен',
                    'inverse' => 'Не е празен',
                ],

                'summary' => [
                    'direct' => ':relationship е празен',
                    'inverse' => ':relationship не е празен',
                ],

            ],

            'is_related_to' => [

                'label' => [

                    'single' => [
                        'direct' => 'Е',
                        'inverse' => 'Не е',
                    ],

                    'multiple' => [
                        'direct' => 'Содржи',
                        'inverse' => 'Не содржи',
                    ],

                ],

                'summary' => [

                    'single' => [
                        'direct' => ':relationship е :values',
                        'inverse' => ':relationship не е :values',
                    ],

                    'multiple' => [
                        'direct' => ':relationship содржи :values',
                        'inverse' => ':relationship не содржи :values',
                    ],

                    'values_glue' => [
                        0 => ', ',
                        'final' => ' или ',
                    ],

                ],

                'form' => [

                    'value' => [
                        'label' => 'Вредност',
                    ],

                    'values' => [
                        'label' => 'Вредности',
                    ],

                ],

            ],

            'form' => [

                'count' => [
                    'label' => 'Број',
                ],

            ],

        ],

        'select' => [

            'is' => [

                'label' => [
                    'direct' => 'Е',
                    'inverse' => 'Не е',
                ],

                'summary' => [
                    'direct' => ':attribute е :values',
                    'inverse' => ':attribute не е :values',
                    'values_glue' => [
                        ', ',
                        'final' => ' или ',
                    ],
                ],

                'form' => [

                    'value' => [
                        'label' => 'Вредност',
                    ],

                    'values' => [
                        'label' => 'Вредности',
                    ],

                ],

            ],

        ],

        'text' => [

            'contains' => [

                'label' => [
                    'direct' => 'Содржи',
                    'inverse' => 'Не содржи',
                ],

                'summary' => [
                    'direct' => ':attribute содржи :text',
                    'inverse' => ':attribute не содржи :text',
                ],

            ],

            'ends_with' => [

                'label' => [
                    'direct' => 'Завршува со',
                    'inverse' => 'Не завршува со',
                ],

                'summary' => [
                    'direct' => ':attribute завршува со :text',
                    'inverse' => ':attribute не завршува со :text',
                ],

            ],

            'equals' => [

                'label' => [
                    'direct' => 'Е еднаков',
                    'inverse' => 'Не е еднаков',
                ],

                'summary' => [
                    'direct' => ':attribute е еднаков на :text',
                    'inverse' => ':attribute не е еднаков на :text',
                ],

            ],

            'is_empty' => [

                'label' => [
                    'direct' => 'Е празен',
                    'inverse' => 'Не е празен',
                ],

                'summary' => [
                    'direct' => ':attribute е празен',
                    'inverse' => ':attribute не е празен',
                ],

            ],

            'starts_with' => [

                'label' => [
                    'direct' => 'Започнува со',
                    'inverse' => 'Не започнува со',
                ],

                'summary' => [
                    'direct' => ':attribute започнува со :text',
                    'inverse' => ':attribute не започнува со :text',
                ],

            ],

            'form' => [

                'text' => [
                    'label' => 'Текст',
                ],

            ],

        ],

    ],

];
