<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['category_name' => 'Food', 'category_id' => 2],
            ['category_name' => 'Apparel & Accessories', 'category_id' => 3],
            ['category_name' => 'Home Appliances', 'category_id' => 6],
            ['category_name' => 'Computer & Office', 'category_id' => 7],
            ['category_name' => 'Home Improvement', 'category_id' => 13],
            ['category_name' => 'Home & Garden', 'category_id' => 15],
            ['category_name' => 'Sports & Entertainment', 'category_id' => 18],
            ['category_name' => 'Office & School Supplies', 'category_id' => 21],
            ['category_name' => 'Toys & Hobbies', 'category_id' => 26],
            ['category_name' => 'Security & Protection', 'category_id' => 30],
            ['category_name' => 'Automobiles, Parts & Accessories', 'category_id' => 34],
            ['category_name' => 'Jewelry & Accessories', 'category_id' => 36],
            ['category_name' => 'Lights & Lighting', 'category_id' => 39],
            ['category_name' => 'Consumer Electronics', 'category_id' => 44],
            ['category_name' => 'Beauty & Health', 'category_id' => 66],
            ['category_name' => 'Weddings & Events', 'category_id' => 320],
            ['category_name' => 'Shoes', 'category_id' => 322],
            ['category_name' => 'Electronic Components & Supplies', 'category_id' => 502],
            ['category_name' => 'Phones & Telecommunications', 'category_id' => 509],
            ['category_name' => 'Test category 06', 'category_id' => 127698009],
            ['category_name' => 'Tools', 'category_id' => 1420],
            ['category_name' => 'Mother & Kids', 'category_id' => 1501],
            ['category_name' => 'Furniture', 'category_id' => 1503],
            ['category_name' => 'Watches', 'category_id' => 1511],
            ['category_name' => 'Luggage & Bags', 'category_id' => 1524],
            ['category_name' => 'Women\'s Clothing', 'category_id' => 200000345],
            ['category_name' => 'Men\'s Clothing', 'category_id' => 200000343],
            ['category_name' => 'Apparel Accessories', 'category_id' => 200000297],
            ['category_name' => 'Hair Extensions & Wigs', 'category_id' => 200165144],
            ['category_name' => 'Special Category', 'category_id' => 200001075],
            ['category_name' => 'Underwear', 'category_id' => 200574005],
            ['category_name' => 'Novelty & Special Use', 'category_id' => 200000532],
            ['category_name' => 'Virtual Products', 'category_id' => 201169612],
            ['category_name' => 'Sports Shoes,Clothing & Accessories', 'category_id' => 201768104],
            ['category_name' => 'Second-Hand', 'category_id' => 201520802],
            ['category_name' => 'Motorcycle Equipments & Parts', 'category_id' => 201355758],
        ]);
    }
}
