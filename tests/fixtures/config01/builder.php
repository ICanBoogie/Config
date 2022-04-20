<?php

namespace Test\ICanBoogie;

return function (SampleConfigBuilder $builder) {

	$builder->add_string("one");
	$builder->bool = false;

};
