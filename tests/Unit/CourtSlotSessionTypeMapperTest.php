<?php

namespace Tests\Unit;

use App\Support\CourtSlotSessionTypeMapper;
use Tests\TestCase;

class CourtSlotSessionTypeMapperTest extends TestCase
{
    public function test_training_maps_to_group_training(): void
    {
        $this->assertSame('group_training', CourtSlotSessionTypeMapper::toSessionType('training'));
    }

    public function test_other_slot_types_pass_through(): void
    {
        $this->assertSame('open_match', CourtSlotSessionTypeMapper::toSessionType('open_match'));
        $this->assertSame('academy_class', CourtSlotSessionTypeMapper::toSessionType('academy_class'));
    }
}
