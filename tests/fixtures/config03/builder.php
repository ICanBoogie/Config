<?php

namespace Test\ICanBoogie;

use Test\ICanBoogie\Builder\SampleBuilder;

return function (SampleBuilder $builder) {

    $builder->add_int(3);
    $builder->bool = true;
};
