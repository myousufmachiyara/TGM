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
            ['id' => 1, 'name' => "Men's Fabric" , 'cat_code' => "M-FAB"],
            ['id' => 2, 'name' => "Men's Finish Goods" , 'cat_code' => "M-FG"],
            ['id' => 3, 'name' => 'Abaya Fabric' , 'cat_code' => "ABY-FAB"],
            ['id' => 4, 'name' => 'Abaya' , 'cat_code' => "ABY"],
            ['id' => 5, 'name' => "Women's Finish Goods" , 'cat_code' => "W-FG"],
            ['id' => 6, 'name' => 'Kids Finish Goods' , 'cat_code' => "K-FG"],
            ['id' => 7, 'name' => 'Accessories' , 'cat_code' => "ACS"],
            ['id' => 8, 'name' => 'Abaya Hijab' , 'cat_code' => "ABY-HIJ"] ,
            ['id' => 9, 'name' => 'Scarf' , 'cat_code' => "SCARF"],
        ]);

        DB::table('product_attributes')->insert([
            ['id' => 1, 'name' => "Size"],
            ['id' => 2, 'name' => "Colors"],
        ]);

        DB::table('product_attributes_values')->insert([
            ['id' => 1, 'product_attribute_id' => 1 , 'value' => "52"],
            ['id' => 2, 'product_attribute_id' => 1 , 'value' => "54"],
            ['id' => 3, 'product_attribute_id' => 1 , 'value' => "56"],
            ['id' => 4, 'product_attribute_id' => 1 , 'value' => "58"],
            ['id' => 5, 'product_attribute_id' => 1 , 'value' => "60"],
            ['id' => 6, 'product_attribute_id' => 2 , 'value' => "Black"],
            ['id' => 7, 'product_attribute_id' => 2 , 'value' => "Blue"],
            ['id' => 8, 'product_attribute_id' => 1 , 'value' => "Free Size"],
            ['id' => 9, 'product_attribute_id' => 1 , 'value' => "Small"],
            ['id' => 10, 'product_attribute_id' => 1 , 'value' => "Medium"],
            ['id' => 11, 'product_attribute_id' => 1 , 'value' => "Large"],
            ['id' => 12, 'product_attribute_id' => 1 , 'value' => "X-Large"]
        ]);  

        DB::table('products')->insert([
            [ 'id' => 1, 'name' => 'Black Nidha', 'sku' => 'FAB-000001', 'description' => '', 'category_id' => 3, 'measurement_unit' => 'yrd', 'item_type' => 'raw',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 0, 'opening_stock' => 1200
            ],
            [ 'id' => 2, 'name' => 'Nidha', 'sku' => 'FAB-000002', 'description' => '', 'category_id' => 3, 'measurement_unit' => 'yrd', 'item_type' => 'raw',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 0, 'opening_stock' => 800
            ],
            [ 'id' => 3, 'name' => 'Alisha', 'sku' => 'ABBY-000001', 'description' => '', 'category_id' => 4, 'measurement_unit' => 'pcs', 'item_type' => 'fg',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 1, 'opening_stock' => 0
            ],
            [ 'id' => 4, 'name' => 'Alisha Hijab', 'sku' => 'ABBY-H-000001', 'description' => '', 'category_id' => 8, 'measurement_unit' => 'pcs', 'item_type' => 'fg',
              'price' => '0.00', 'sale_price' => '0.00', 'purchase_note' => '', 'has_variations' => 1, 'opening_stock' => 0
            ]
        ]);

        DB::table('product_variations')->insert([
            ['id' => 1, 'product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 1, 'sku' => 'ABBY-000001-52','price' => '1200.00','stock' => 50,],
            ['id' => 2, 'product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 2, 'sku' => 'ABBY-000001-54','price' => '1200.00','stock' => 50,],
            ['id' => 3, 'product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 3, 'sku' => 'ABBY-000001-56','price' => '1200.00','stock' => 50,],
            ['id' => 4, 'product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 4, 'sku' => 'ABBY-000001-58','price' => '1200.00','stock' => 50,],
            ['id' => 5, 'product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 5, 'sku' => 'ABBY-000001-60','price' => '1200.00','stock' => 50,],
            ['id' => 6, 'product_id' => 4, 'attribute_id' => 1, 'attribute_value_id' => 8, 'sku' => 'ABBY-H-000001-FS','price' => '300.00','stock' => 50,],
        ]);
    }
}
