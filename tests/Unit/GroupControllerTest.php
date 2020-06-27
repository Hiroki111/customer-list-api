<?php

namespace Tests\Unit;

use App\Group;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function canGroups()
    {
        foreach (range(1, 100) as $i) {
            factory(Group::class)->create();
        }

        $res = $this->get("/api/groups");

        $res->assertStatus(200)
            ->assertJson([
                'groups' => Group::all()->toArray(),
            ]);
    }
}
