<?php

use Test\ICanBoogie\Builder\SampleBuilder;

return fn(SampleBuilder $builder) => $builder
    ->add_string("two")
    ->add_int(456);
