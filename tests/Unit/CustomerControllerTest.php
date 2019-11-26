<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $defaultPageSize = 10;

    /** @test */
    public function shouldGetFirstNCustomers()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }

        $res = $this->get("/api/customers");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::take($this->defaultPageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function shouldGetCustomersWithSize()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }
        $pageSize = 15;

        $res = $this->get("/api/customers?pageSize=$pageSize");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::take($pageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function shouldGetCustomersWithIndex()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }
        $start = 10;

        $res = $this->get("/api/customers?start=$start");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::where('id', '>=', $start)->take($this->defaultPageSize)->get()->toArray(),
            ]);
    }

    /** @test */
    public function shouldGetCustomersWithKeyword()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create(['name' => ['Alice', 'Bob', 'Carol'][rand(0, 2)]]);
        }
        $c1 = factory(Customer::class)->create(['name' => 'john']);
        $c2 = factory(Customer::class)->create(['name' => 'JOHN']);
        $c3 = factory(Customer::class)->create(['name' => 'Johny']);
        $c4 = factory(Customer::class)->create(['name' => 'Johannes']);
        $c5 = factory(Customer::class)->create(['name' => 'Elton John']);

        $keyword = "john";

        $res = $this->get("/api/customers?keyword=$keyword");

        $res->assertStatus(201)
            ->assertJson([
                'data' => Customer::where('name', 'LIKE', $keyword)->take($this->defaultPageSize)->get()->toArray(),
            ]);
    }
}
