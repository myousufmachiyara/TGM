<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ChartOfAccounts;
use App\Models\HeadOfAccounts;
use App\Models\ProductAttributes;
use App\Models\ProductAttributesValues;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\ProductVariations;
use App\Models\SubHeadOfAccounts;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now(); // Get the current timestamp

        User::insert([
            'name' => 'admin',
            'email' => 'admin@tgm.com',
            'password' => Hash::make('Arefifth@12'),
        ]);

        HeadOfAccounts::insert([
            ['id' => 1, 'name' => 'Assets'],
            ['id' => 2, 'name' => 'Liabilities'],
            ['id' => 3, 'name' => 'Expenses'],
            ['id' => 4, 'name' => 'Revenue'],
            ['id' => 5, 'name' => 'Equity'],
        ]);

        SubHeadOfAccounts::insert([
            ['id' => 1, 'hoa_id' => 1, 'name' => 'Current Assets'],
            ['id' => 2, 'hoa_id' => 1, 'name' => 'Inventory'],
            ['id' => 3, 'hoa_id' => 2, 'name' => 'Current Liabilities'],
            ['id' => 4, 'hoa_id' => 2, 'name' => 'Long-Term Liabilities'],
            ['id' => 5, 'hoa_id' => 4, 'name' => 'Sales'],
            ['id' => 6, 'hoa_id' => 3, 'name' => 'Expenses'],
            ['id' => 7, 'hoa_id' => 5, 'name' => 'Equity'],
        ]);

        ChartOfAccounts::insert([
            ['id' => 1, 'shoa_id' => 1, 'name' => 'Cash', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Asset', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'shoa_id' => 1, 'name' => 'Bank', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Asset', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'shoa_id' => 1, 'name' => 'Accounts Receivable', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Customer Accounts', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'shoa_id' => 2, 'name' => 'Raw Material Inventory', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Inventory', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'shoa_id' => 2, 'name' => 'Finished Goods Inventory', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Inventory', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'shoa_id' => 3, 'name' => 'Accounts Payable', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Supplier Accounts', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'shoa_id' => 5, 'name' => 'Sale Account', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Revenue', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'shoa_id' => 6, 'name' => 'Expense Account', 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Expense', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'shoa_id' => 7, 'name' => "Owner's Equity", 'receivables' => '0', 'payables' => '0', 'opening_date' => '2025-01-01', 'remarks' => 'Equity', 'address' => '', 'phone_no' => '', 'created_at' => $now, 'updated_at' => $now],
        ]);

        ProductCategory::insert([
            ['id' => 1, 'name' => 'Abaya Fabric', 'cat_code' => 'ABBY-FAB'],
            ['id' => 2, 'name' => 'Abaya', 'cat_code' => 'ABBY'],
            ['id' => 3, 'name' => 'Abaya Hijab', 'cat_code' => 'ABBY-HIJ'],
            ['id' => 4, 'name' => "Kid's Abaya", 'cat_code' => 'K-ABBY'],
            ['id' => 5, 'name' => 'Scarf', 'cat_code' => 'SCF'],
            ['id' => 6, 'name' => 'Ladies FG', 'cat_code' => 'L-FG'],
            ['id' => 7, 'name' => "Men's Fancy Fabric", 'cat_code' => 'M-FAB-F'],
            ['id' => 8, 'name' => "Men's Plain Fabric", 'cat_code' => 'M-FAB-P'],
            ['id' => 9, 'name' => 'Kids FG', 'cat_code' => 'K-FG'],
            ['id' => 10, 'name' => 'Accessories', 'cat_code' => 'ACS'],
            ['id' => 11, 'name' => 'Kameez Shalwar Plain', 'cat_code' => 'KAS-P'],
            ['id' => 12, 'name' => 'Kameez Shalwar Design', 'cat_code' => 'KAS-D'],
            ['id' => 13, 'name' => 'Kameez Shalwar C.E', 'cat_code' => 'KAS-CE'],
            ['id' => 14, 'name' => 'Kameez Shalwar H.W', 'cat_code' => 'KAS-HW'],
            ['id' => 15, 'name' => 'Kurta Shalwar Plain', 'cat_code' => 'KUS-P'],
            ['id' => 16, 'name' => 'Kurta Shalwar Design', 'cat_code' => 'KUS-D'],
            ['id' => 17, 'name' => 'Kurta Shalwar C.E', 'cat_code' => 'KUS-CE'],
            ['id' => 18, 'name' => 'Kurta Shalwar H.W', 'cat_code' => 'KUS-HW'],
            ['id' => 19, 'name' => 'Kurta Chicken', 'cat_code' => 'K-C'],
            ['id' => 20, 'name' => 'Kurta Fancy', 'cat_code' => 'K-F'],
            ['id' => 21, 'name' => 'Kurta C.E', 'cat_code' => 'K-CE'],
            ['id' => 22, 'name' => 'Kurta H.W', 'cat_code' => 'K-HW'],
            ['id' => 23, 'name' => 'Kurta Pajama Plain', 'cat_code' => 'KPJ-P'],
            ['id' => 24, 'name' => 'Kurta Pajama Fancy', 'cat_code' => 'KPJ-F'],
            ['id' => 25, 'name' => '3PC Suit Plain', 'cat_code' => '3PC-P'],
            ['id' => 26, 'name' => '3PC Suit Fancy', 'cat_code' => '3PC-F'],
            ['id' => 27, 'name' => 'WC Plain', 'cat_code' => 'WC-P'],
            ['id' => 28, 'name' => 'WC Messuri', 'cat_code' => 'WC-M'],
            ['id' => 29, 'name' => 'WC Jamawar', 'cat_code' => 'WC-JW'],
            ['id' => 30, 'name' => 'WC Raw Silk', 'cat_code' => 'WC-RS'],
            ['id' => 31, 'name' => 'WC Jute', 'cat_code' => 'WC-J'],
            ['id' => 32, 'name' => 'WC Suiting', 'cat_code' => 'WC-S'],
            ['id' => 33, 'name' => 'PC Plain', 'cat_code' => 'PC-P'],
            ['id' => 34, 'name' => 'PC Messuri', 'cat_code' => 'PC-M'],
            ['id' => 35, 'name' => 'PC Jamawar', 'cat_code' => 'PC-JW'],
            ['id' => 36, 'name' => 'Pajama Plain', 'cat_code' => 'PJ-P'],
            ['id' => 37, 'name' => 'Pajama Pocket', 'cat_code' => 'PJ-PKT'],
            ['id' => 38, 'name' => 'Shalwar Plain', 'cat_code' => 'SHALWAR-P'],
            ['id' => 39, 'name' => 'Sherwani Plain', 'cat_code' => 'SHER-P'],
            ['id' => 40, 'name' => 'Sherwani Embroidery', 'cat_code' => 'SHER-E'],
            ['id' => 41, 'name' => 'Coat Casual', 'cat_code' => 'CC'],
            ['id' => 42, 'name' => 'Coat Formal', 'cat_code' => 'CF'],
            ['id' => 43, 'name' => 'Shawl', 'cat_code' => 'SHAWL'],
        ]);

        ProductAttributes::insert([
            ['id' => 1, 'name' => 'Size'],
            ['id' => 2, 'name' => 'Colors'],
        ]);

        ProductAttributesValues::insert([
            ['id' => 1, 'product_attribute_id' => 1, 'value' => '52'],
            ['id' => 2, 'product_attribute_id' => 1, 'value' => '54'],
            ['id' => 3, 'product_attribute_id' => 1, 'value' => '56'],
            ['id' => 4, 'product_attribute_id' => 1, 'value' => '58'],
            ['id' => 5, 'product_attribute_id' => 1, 'value' => '60'],
            ['id' => 6, 'product_attribute_id' => 1, 'value' => 'Free Size'],
            ['id' => 7, 'product_attribute_id' => 1, 'value' => 'Small'],
            ['id' => 8, 'product_attribute_id' => 1, 'value' => 'Medium'],
            ['id' => 9, 'product_attribute_id' => 1, 'value' => 'Large'],
            ['id' => 10, 'product_attribute_id' => 1, 'value' => 'X-Large'],
            ['id' => 11, 'product_attribute_id' => 2, 'value' => 'Black'],
            ['id' => 12, 'product_attribute_id' => 2, 'value' => 'Blue'],
            ['id' => 13, 'product_attribute_id' => 2, 'value' => 'Yellow'],
            ['id' => 14, 'product_attribute_id' => 2, 'value' => 'Green'],
            ['id' => 15, 'product_attribute_id' => 2, 'value' => 'Orange'],
            ['id' => 16, 'product_attribute_id' => 2, 'value' => 'Purple'],
            ['id' => 17, 'product_attribute_id' => 2, 'value' => 'Red-Orange'],
            ['id' => 18, 'product_attribute_id' => 2, 'value' => 'Yellow-Orange'],
            ['id' => 19, 'product_attribute_id' => 2, 'value' => 'Yellow-Green'],
            ['id' => 20, 'product_attribute_id' => 2, 'value' => 'Blue-Green'],
            ['id' => 21, 'product_attribute_id' => 2, 'value' => 'Blue-Purple'],
            ['id' => 22, 'product_attribute_id' => 2, 'value' => 'Red-Purple'],
            ['id' => 23, 'product_attribute_id' => 2, 'value' => 'Crimson'],
            ['id' => 24, 'product_attribute_id' => 2, 'value' => 'Maroon'],
            ['id' => 25, 'product_attribute_id' => 2, 'value' => 'Scarlet'],
            ['id' => 26, 'product_attribute_id' => 2, 'value' => 'Burgundy'],
            ['id' => 27, 'product_attribute_id' => 2, 'value' => 'Navy Blue'],
            ['id' => 28, 'product_attribute_id' => 2, 'value' => 'Sky Blue'],
            ['id' => 29, 'product_attribute_id' => 2, 'value' => 'Cobalt Blue'],
            ['id' => 30, 'product_attribute_id' => 2, 'value' => 'Teal'],
            ['id' => 31, 'product_attribute_id' => 2, 'value' => 'Olive Green'],
            ['id' => 32, 'product_attribute_id' => 2, 'value' => 'Lime Green'],
            ['id' => 33, 'product_attribute_id' => 2, 'value' => 'Forest Green'],
            ['id' => 34, 'product_attribute_id' => 2, 'value' => 'Emerald Green'],
            ['id' => 35, 'product_attribute_id' => 2, 'value' => 'Mustard Yellow'],
            ['id' => 36, 'product_attribute_id' => 2, 'value' => 'Gold'],
            ['id' => 37, 'product_attribute_id' => 2, 'value' => 'Lemon Yellow'],
            ['id' => 38, 'product_attribute_id' => 2, 'value' => 'Lavender'],
            ['id' => 39, 'product_attribute_id' => 2, 'value' => 'Violet'],
            ['id' => 40, 'product_attribute_id' => 2, 'value' => 'Plum'],
            ['id' => 41, 'product_attribute_id' => 2, 'value' => 'Magenta'],
            ['id' => 42, 'product_attribute_id' => 2, 'value' => 'Peach'],
            ['id' => 43, 'product_attribute_id' => 2, 'value' => 'Coral'],
            ['id' => 44, 'product_attribute_id' => 2, 'value' => 'Amber'],
            ['id' => 45, 'product_attribute_id' => 2, 'value' => 'Baby Pink'],
            ['id' => 46, 'product_attribute_id' => 2, 'value' => 'Hot Pink'],
            ['id' => 47, 'product_attribute_id' => 2, 'value' => 'Salmon'],
            ['id' => 48, 'product_attribute_id' => 2, 'value' => 'Rose'],
            ['id' => 49, 'product_attribute_id' => 2, 'value' => 'White'],
            ['id' => 50, 'product_attribute_id' => 2, 'value' => 'Gray'],
            ['id' => 51, 'product_attribute_id' => 2, 'value' => 'Beige'],
            ['id' => 52, 'product_attribute_id' => 2, 'value' => 'Brown'],
            ['id' => 53, 'product_attribute_id' => 2, 'value' => 'Ivory'],
            ['id' => 54, 'product_attribute_id' => 2, 'value' => 'Silver'],
            ['id' => 55, 'product_attribute_id' => 2, 'value' => 'Bronze'],
            ['id' => 56, 'product_attribute_id' => 2, 'value' => 'Copper'],
            ['id' => 57, 'product_attribute_id' => 2, 'value' => 'Pastel Blue'],
            ['id' => 58, 'product_attribute_id' => 2, 'value' => 'Pastel Pink'],
            ['id' => 59, 'product_attribute_id' => 2, 'value' => 'Pastel Green'],
            ['id' => 60, 'product_attribute_id' => 2, 'value' => 'Pastel Yellow'],
            ['id' => 61, 'product_attribute_id' => 2, 'value' => 'Neon Green'],
            ['id' => 62, 'product_attribute_id' => 2, 'value' => 'Neon Pink'],
            ['id' => 63, 'product_attribute_id' => 2, 'value' => 'Neon Orange'],
            ['id' => 64, 'product_attribute_id' => 2, 'value' => 'Neon Blue'],
            ['id' => 65, 'product_attribute_id' => 2, 'value' => 'Offwhite'],
            ['id' => 66, 'product_attribute_id' => 2, 'value' => 'Cream'],
            ['id' => 67, 'product_attribute_id' => 2, 'value' => 'Fawn'],
            ['id' => 68, 'product_attribute_id' => 2, 'value' => 'Teal Blue'],
            ['id' => 69, 'product_attribute_id' => 2, 'value' => 'Light Green'],
            ['id' => 70, 'product_attribute_id' => 2, 'value' => 'Malaysian Grey'],
            ['id' => 71, 'product_attribute_id' => 2, 'value' => 'Skin'],
            ['id' => 72, 'product_attribute_id' => 2, 'value' => 'Light Grey'],
            ['id' => 73, 'product_attribute_id' => 2, 'value' => 'Dark Grey'],
            ['id' => 74, 'product_attribute_id' => 2, 'value' => 'Mehendi'],
            ['id' => 75, 'product_attribute_id' => 2, 'value' => 'Camel'],
            ['id' => 76, 'product_attribute_id' => 2, 'value' => 'Pista'],
            ['id' => 77, 'product_attribute_id' => 2, 'value' => 'Light Purple'],
            ['id' => 78, 'product_attribute_id' => 2, 'value' => 'Light Pink'],
        ]);
    }
}
