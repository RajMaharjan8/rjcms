<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Rjcodes\Rjcms\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');
