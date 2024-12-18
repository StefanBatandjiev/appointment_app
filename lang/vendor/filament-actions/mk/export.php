<?php

return [

    'label' => 'Извези :label',

    'modal' => [

        'heading' => 'Извези :label',

        'form' => [

            'columns' => [

                'label' => 'Колони',

                'form' => [

                    'is_enabled' => [
                        'label' => ':column активирано',
                    ],

                    'label' => [
                        'label' => ':column ознака',
                    ],

                ],

            ],

        ],

        'actions' => [

            'export' => [
                'label' => 'Извези',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'Извозот е завршен',

            'actions' => [

                'download_csv' => [
                    'label' => 'Превземи .csv',
                ],

                'download_xlsx' => [
                    'label' => 'Превземи .xlsx',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'Извозот е премногу голем',
            'body' => 'Не можете да извезете повеќе од 1 ред одеднаш.|Не можете да извезете повеќе од :count реда одеднаш.',
        ],

        'started' => [
            'title' => 'Извозот започна',
            'body' => 'Вашиот извоз започна и 1 ред ќе биде обработен во позадината. Ќе добиете известување со линк за превземање кога ќе заврши.|Вашиот извоз започна и :count реда ќе бидат обработени во позадината. Ќе добиете известување со линк за превземање кога ќе заврши.',
        ],

    ],

    'file_name' => 'извоз-:export_id-:model',

];
