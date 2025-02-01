<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('head_of_accounts')->insert([
            ['id' => 1, 'name' => 'Assets'],
            ['id' => 2, 'name' => 'Liabilities'],
            ['id' => 3, 'name' => 'Expenses'],
            ['id' => 4, 'name' => 'Revenue'],
            ['id' => 5, 'name' => 'Equity'],
        ]);

        DB::table('product_categories')->insert([
            ['id' => 1, 'name' => "Men's Fabric"],
            ['id' => 2, 'name' => "Men's Finish Goods"],
            ['id' => 3, 'name' => 'Abaya Fabric'],
            ['id' => 4, 'name' => 'Abaya'],
            ['id' => 5, 'name' => 'Ladies Finish Goods'],
            ['id' => 6, 'name' => 'Kids Finish Goods'],
            ['id' => 7, 'name' => 'Accessories'],
        ]);

        DB::table('product_attributes')->insert([
            ['id' => 1, 'name' => "Size"],
            ['id' => 2, 'name' => "Colors"],
        ]);

        DB::table('product_attributes_values')->insert([
            ['id' => 1, 'product_attributes_id' => 1 , 'value' => "52"],
            ['id' => 2, 'product_attributes_id' => 1 , 'value' => "54"],
            ['id' => 3, 'product_attributes_id' => 1 , 'value' => "56"],
            ['id' => 4, 'product_attributes_id' => 1 , 'value' => "58"],
            ['id' => 5, 'product_attributes_id' => 1 , 'value' => "60"],
            ['id' => 6, 'product_attributes_id' => 2 , 'value' => "Black"],
            ['id' => 7, 'product_attributes_id' => 2 , 'value' => "Blue"],
        ]);  

        DB::table('sub_head_of_accounts')->insert([
            ['id' => 1, 'hoa_id' => 2 , 'name' => "Payables"],
        ]);

        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'shoa_id' => 1 , 'name' => "Test Supplier" , 
            'receivables' => "0" , 'payables' => "100000", 
            'opening_date' => "2025-01-29", 'remarks' => "Test Supplier", 
            'address' => "Karachi", 'phone_no' => "03211234567", 'credit_limit' => "10", 'credit_limit' => "10"],
        ]);
    }
}
