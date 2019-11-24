<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function canGetAllCustomers()
    {
        foreach (range(1, 1000) as $i) {
            factory(Customer::class)->create();
        }

        $res = $this->get('/api/customers');

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::all()->toArray(),
            ]);
    }
}
