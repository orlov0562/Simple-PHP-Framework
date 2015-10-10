<?php

    return [
        'unsigned' => ['App\Routes\Validators\BaseValidator', 'isUnsigned'],
        'notEmpty' => ['App\Routes\Validators\BaseValidator', 'isNotEmpty'],

        'regexp' => ['App\Routes\Validators\RegExpValidator', 'isMatched'],
    ];
