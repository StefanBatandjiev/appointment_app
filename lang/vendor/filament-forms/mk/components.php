<?php

return [

    'builder' => [

        'actions' => [

            'clone' => [
                'label' => 'Клонирај',
            ],

            'add' => [

                'label' => 'Додај во :label',

                'modal' => [

                    'heading' => 'Додај во :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Додај',
                        ],

                    ],

                ],

            ],

            'add_between' => [

                'label' => 'Вметни помеѓу блокови',

                'modal' => [

                    'heading' => 'Додај во :label',

                    'actions' => [

                        'add' => [
                            'label' => 'Додај',
                        ],

                    ],

                ],

            ],

            'delete' => [
                'label' => 'Избриши',
            ],

            'edit' => [

                'label' => 'Измени',

                'modal' => [

                    'heading' => 'Измени блок',

                    'actions' => [

                        'save' => [
                            'label' => 'Зачувај промени',
                        ],

                    ],

                ],

            ],

            'reorder' => [
                'label' => 'Премести',
            ],

            'move_down' => [
                'label' => 'Премести надолу',
            ],

            'move_up' => [
                'label' => 'Премести нагоре',
            ],

            'collapse' => [
                'label' => 'Собери',
            ],

            'expand' => [
                'label' => 'Проширете',
            ],

            'collapse_all' => [
                'label' => 'Собери сите',
            ],

            'expand_all' => [
                'label' => 'Проширете сите',
            ],

        ],

    ],

    'checkbox_list' => [

        'actions' => [

            'deselect_all' => [
                'label' => 'Отбери ги сите',
            ],

            'select_all' => [
                'label' => 'Одбери ги сите',
            ],

        ],

    ],

    'file_upload' => [

        'editor' => [

            'actions' => [

                'cancel' => [
                    'label' => 'Откажи',
                ],

                'drag_crop' => [
                    'label' => 'Режим повлечи "сечи"',
                ],

                'drag_move' => [
                    'label' => 'Режим повлечи "премести"',
                ],

                'flip_horizontal' => [
                    'label' => 'Заврти слика хоризонтално',
                ],

                'flip_vertical' => [
                    'label' => 'Заврти слика вертикално',
                ],

                'move_down' => [
                    'label' => 'Премести слика надолу',
                ],

                'move_left' => [
                    'label' => 'Премести слика налево',
                ],

                'move_right' => [
                    'label' => 'Премести слика надесно',
                ],

                'move_up' => [
                    'label' => 'Премести слика нагоре',
                ],

                'reset' => [
                    'label' => 'Ресетирај',
                ],

                'rotate_left' => [
                    'label' => 'Заврти слика налево',
                ],

                'rotate_right' => [
                    'label' => 'Заврти слика надесно',
                ],

                'set_aspect_ratio' => [
                    'label' => 'Постави сооднос на сликата на :ratio',
                ],

                'save' => [
                    'label' => 'Зачувај',
                ],

                'zoom_100' => [
                    'label' => 'Зум на слика до 100%',
                ],

                'zoom_in' => [
                    'label' => 'Зум во',
                ],

                'zoom_out' => [
                    'label' => 'Зум надвор',
                ],

            ],

            'fields' => [

                'height' => [
                    'label' => 'Висина',
                    'unit' => 'px',
                ],

                'rotation' => [
                    'label' => 'Ротација',
                    'unit' => 'deg',
                ],

                'width' => [
                    'label' => 'Ширина',
                    'unit' => 'px',
                ],

                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],

                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],

            ],

            'aspect_ratios' => [

                'label' => 'Соодноси на слика',

                'no_fixed' => [
                    'label' => 'Слободно',
                ],

            ],

            'svg' => [

                'messages' => [
                    'confirmation' => 'Уредувањето на SVG фајлови не се препорачува, бидејќи може да резултира со губење на квалитетот при скалирање.\n Дали сте сигурни дека сакате да продолжите?',
                    'disabled' => 'Уредувањето на SVG фајлови е исклучено, бидејќи може да резултира со губење на квалитетот при скалирање.',
                ],

            ],

        ],

    ],

    'key_value' => [

        'actions' => [

            'add' => [
                'label' => 'Додај ред',
            ],

            'delete' => [
                'label' => 'Избриши ред',
            ],

            'reorder' => [
                'label' => 'Прераспореди ред',
            ],

        ],

        'fields' => [

            'key' => [
                'label' => 'Клуч',
            ],

            'value' => [
                'label' => 'Вредност',
            ],

        ],

    ],

    'markdown_editor' => [

        'toolbar_buttons' => [
            'attach_files' => 'Прикачи фајлови',
            'blockquote' => 'Цитат',
            'bold' => 'Дебел',
            'bullet_list' => 'Список со точки',
            'code_block' => 'Код блок',
            'heading' => 'Наслов',
            'italic' => 'Косо',
            'link' => 'Линк',
            'ordered_list' => 'Нумериран список',
            'redo' => 'Повтори',
            'strike' => 'Препречи',
            'table' => 'Маса',
            'undo' => 'Поврати',
        ],

    ],

    'radio' => [

        'boolean' => [
            'true' => 'Да',
            'false' => 'Не',
        ],

    ],

    'repeater' => [

        'actions' => [

            'add' => [
                'label' => 'Додај во :label',
            ],

            'add_between' => [
                'label' => 'Вметни помеѓу',
            ],

            'delete' => [
                'label' => 'Избриши',
            ],

            'clone' => [
                'label' => 'Клонирај',
            ],

            'reorder' => [
                'label' => 'Премести',
            ],

            'move_down' => [
                'label' => 'Премести надолу',
            ],

            'move_up' => [
                'label' => 'Премести нагоре',
            ],

            'collapse' => [
                'label' => 'Собери',
            ],

            'expand' => [
                'label' => 'Проширете',
            ],

            'collapse_all' => [
                'label' => 'Собери сите',
            ],

            'expand_all' => [
                'label' => 'Проширете сите',
            ],

        ],

    ],

    'rich_editor' => [

        'dialogs' => [

            'link' => [

                'actions' => [
                    'link' => 'Линк',
                    'unlink' => 'Отстрани линк',
                ],

                'label' => 'URL',

                'placeholder' => 'Внесете URL',

            ],

        ],

        'toolbar_buttons' => [
            'attach_files' => 'Прикачи фајлови',
            'blockquote' => 'Цитат',
            'bold' => 'Дебел',
            'bullet_list' => 'Список со точки',
            'code_block' => 'Код блок',
            'h1' => 'Наслов',
            'h2' => 'Наслов',
            'h3' => 'Поднаслов',
            'italic' => 'Косо',
            'link' => 'Линк',
            'ordered_list' => 'Нумериран список',
            'redo' => 'Повтори',
            'strike' => 'Препречи',
            'underline' => 'Подвлечи',
            'undo' => 'Поврати',
        ],

    ],

    'select' => [

        'actions' => [

            'create_option' => [

                'modal' => [

                    'heading' => 'Создај',

                    'actions' => [

                        'create' => [
                            'label' => 'Создај',
                        ],

                        'cancel' => [
                            'label' => 'Откажи',
                        ],

                    ],

                ],

            ],

        ],

    ],

    'toggle' => [

        'actions' => [

            'toggle_on' => [
                'label' => 'Вклучи',
            ],

            'toggle_off' => [
                'label' => 'Исклучи',
            ],

        ],

    ],

    'datetime' => [

        'actions' => [

            'now' => [
                'label' => 'Сега',
            ],

        ],

    ],

    'color_picker' => [

        'actions' => [

            'clear' => [
                'label' => 'Избриши',
            ],

        ],

    ],

];
