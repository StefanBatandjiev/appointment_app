<?php

return [

    'label' => 'Импорт :label',

    'modal' => [

        'heading' => 'Импорт :label',

        'form' => [

            'file' => [

                'label' => 'Фајл',

                'placeholder' => 'Прикачете CSV фајл',

                'rules' => [
                    'duplicate_columns' => '{0} Фајлот не смее да содржи повеќе од еден празен заглавие на колона.|{1,*} Фајлот не смее да содржи дупликати на заглавија на колони: :columns.',
                ],

            ],

            'columns' => [
                'label' => 'Колони',
                'placeholder' => 'Изберете колона',
            ],

        ],

        'actions' => [

            'download_example' => [
                'label' => 'Превземете пример CSV фајл',
            ],

            'import' => [
                'label' => 'Импорт',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'Импортот заврши',

            'actions' => [

                'download_failed_rows_csv' => [
                    'label' => 'Превземете информации за неуспешниот ред|Превземете информации за неуспешните редови',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'Прикачениот CSV фајл е премногу голем',
            'body' => 'Не можете да импортирате повеќе од 1 ред одеднаш.|Не можете да импортирате повеќе од :count реда одеднаш.',
        ],

        'started' => [
            'title' => 'Импортот започна',
            'body' => 'Вашиот импорт започна и 1 ред ќе се обработи во позадината.|Вашиот импорт започна и :count реда ќе се обработат во позадината.',
        ],

    ],

    'example_csv' => [
        'file_name' => ':importer-example',
    ],

    'failure_csv' => [
        'file_name' => 'import-:import_id-:csv_name-neuspešni-редови',
        'error_header' => 'грешка',
        'system_error' => 'Системска грешка, ве молиме контактирајте ја поддршката.',
        'column_mapping_required_for_new_record' => 'Колоната :attribute не е мапирана на колона во фајлот, но е потребна за создавање нови записи.',
    ],

];
