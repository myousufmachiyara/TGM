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
        
        DB::table('sub_head_of_accounts')->insert([
            ['id' => 1, 'hoa_id' => 1 , 'name' => "Current Assets"],
            ['id' => 2, 'hoa_id' => 1 , 'name' => "Inventory"],
            ['id' => 3, 'hoa_id' => 2 , 'name' => "Current Liabilities"],
            ['id' => 4, 'hoa_id' => 2 , 'name' => "Long-Term Liabilities"],
            ['id' => 5, 'hoa_id' => 4 , 'name' => "Sales"],
            ['id' => 6, 'hoa_id' => 3 , 'name' => "Expenses"],
            ['id' => 7, 'hoa_id' => 5 , 'name' => "Equity"],
        ]);
        
        DB::table('chart_of_accounts')->insert([
            ['id' => 1, 'shoa_id' => 1 , 'name' => "Cash", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Asset", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 2, 'shoa_id' => 1 , 'name' => "Bank", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Asset", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 3, 'shoa_id' => 1 , 'name' => "Accounts Receivable", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Customer Accounts", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"], 
            ['id' => 4, 'shoa_id' => 2 , 'name' => "Raw Material Inventory", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Inventory", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 5, 'shoa_id' => 2 , 'name' => "Finished Goods Inventory", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Inventory", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 6, 'shoa_id' => 3 , 'name' => "Accounts Payable", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Supplier Accounts", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"], 
            ['id' => 7, 'shoa_id' => 5 , 'name' => "Sale Account", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Revenue", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 8, 'shoa_id' => 6 , 'name' => "Expense Account", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Expense", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"],
            ['id' => 9, 'shoa_id' => 7 , 'name' => "Owner's Equity", 'receivables' => "0", 'payables' => "0", 'opening_date' => "2025-01-01", 'remarks' => "Equity", 'address' => "", 'phone_no' => "", 'credit_limit' => "0"], 
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

        DB::table('products')->insert([
            [ 'id' => 1, 'name' => 'Black Nidha', 'sku' => 'FAB-000001', 'description' => '', 'category_id' => 3, 'measurement_unit' => 'yrd', 'item_type' => 'raw',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 0, 'opening_stock' => 1200
            ],
            [ 'id' => 2, 'name' => 'Nidha', 'sku' => 'FAB-000002', 'description' => '', 'category_id' => 3, 'measurement_unit' => 'yrd', 'item_type' => 'raw',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 0, 'opening_stock' => 800
            ],
        ]);
    }
}
