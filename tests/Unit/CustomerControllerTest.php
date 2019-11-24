<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $pageSize = 10;

    /** @test */
    public function shouldGetFirstNCustomers()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }

        $res = $this->get("/api/customers");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::take($this->pageSize)->get()->toArray(),
            ]);
    }
}
