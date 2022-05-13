<?php

namespace Test\ICanBoogie;

use Test\ICanBoogie\Builder\SampleBuilder;

return function (SampleBuilder $builder) {

    $builder->add_string("one");
    $builder->bool = false;
};
