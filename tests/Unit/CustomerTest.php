<?php

namespace Tests\Unit;

use App\Customer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function shouldGetCustomerList()
    {
        $customerIds = [];
        foreach (range(1, 100) as $i) {
            $customerIds[] = factory(Customer::class)->create()->id;
        }
        $customerIds = collect($customerIds)->filter(function ($id) {
            return $id <= 10;
        })->all();

        $this->assertEquals($customerIds, Customer::getCustomerList()->map(function ($customer) {
            return $customer->id;
        })->all());
    }

    /** @test */
    public function shouldGetCustomerListWithArguments()
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

        $customerIds = Customer::getCustomerList(5, 11, "john")
            ->map(function ($customer) {
                return $customer->id;
            })->all();

        $this->assertEquals([$id1, $id2, $id3, $id4, $id5], $customerIds);
    }
}
