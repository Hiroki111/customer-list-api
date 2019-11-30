<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function canGetCustomerList()
    {
        foreach (range(1, 100) as $i) {
            factory(Customer::class)->create();
        }
        $size = Customer::getDefaultPageSize();
        $customerIds = Customer::take($size)->pluck('id')->all();

        $results = Customer::getCustomerList();
        $customerIdsFromList = collect($results->items())->map(function ($customer) {
            return $customer->id;
        })->all();

        $this->assertEquals($customerIds, $customerIdsFromList);
        $this->assertJson($size, $results->count());
        $this->assertJson(100, $results->total());
    }

    /** @test */
    public function canGetCustomerListWithArguments()
    {
        foreach (range(1, 10) as $i) {
            factory(Customer::class)->create(['name' => ['Alice', 'Bob', 'Carol'][rand(0, 2)]]);
        }
        $id1 = factory(Customer::class)->create(['name' => 'john'])->id;
        $id2 = factory(Customer::class)->create(['name' => 'Johny'])->id;
        $id3 = factory(Customer::class)->create(['name' => 'Elton John'])->id;
        foreach (range(1, 10) as $i) {
            factory(Customer::class)->create(['name' => ['Alice', 'Bob', 'Carol'][rand(0, 2)]]);
        }
        $id4 = factory(Customer::class)->create(['name' => 'john'])->id;
        $id5 = factory(Customer::class)->create(['name' => 'Johny'])->id;
        $id6 = factory(Customer::class)->create(['name' => 'Elton John'])->id;

        $results = Customer::getCustomerList(5, "john");
        $customerIds = collect($results->items())
            ->map(function ($customer) {
                return $customer->id;
            })->all();

        $this->assertEquals([$id1, $id2, $id3, $id4, $id5], $customerIds);
        $this->assertJson(5, $results->count());
        $this->assertJson(6, $results->total());
    }
}
