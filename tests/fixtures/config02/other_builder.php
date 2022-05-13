<?php

use Test\ICanBoogie\Builder\SampleBuilder;

return function (SampleBuilder $builder) {

    $builder->add_string("two");
    $builder->add_int(456);
};
