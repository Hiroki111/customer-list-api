<?php

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sizeOfGroupsTable = App\Group::all()->count();
        foreach (range(1, 100) as $i) {
            factory(App\Customer::class)->create([
                'group_id' => rand(0, $sizeOfGroupsTable),
            ]);
        }
    }
}
