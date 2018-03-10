<?php

return [
    'DummyStringInTheStub1' => 'ReplacedWithThisString',
    'DummyStringInTheStub2' => function () {
        return 'ReplacedWithReturnValueOfCallback';
    },
];