<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Helpers\TeHelper;

/*
This test case tests the logic in the willExpireAt method by providing $due_time and $created_at values. It then uses the assertEquals method to check if the $expected_result matches the value returned by willExpireAt.
*/
class TeHelperTest extends TestCase
{
    public function testWillExpireAt()
    {
        $due_time = Carbon::now()->addHours(2);
        $created_at = Carbon::now();
        
        $expected = $due_time->format('Y-m-d H:i:s');
        $result = TeHelper::willExpireAt($due_time, $created_at);
        $this->assertEquals($expected, $result);
    }
}
