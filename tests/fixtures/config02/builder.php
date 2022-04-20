<?php

namespace Test\ICanBoogie;

return function (SampleConfigBuilder $builder) {

	$builder->add_string("two");
	$builder->add_int(2);
	$builder->bool = false;

};
