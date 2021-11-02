<?php

namespace Database\Seeders;

use Flynsarmy\CsvSeeder\CsvSeeder;
use Carbon\Carbon;

class ProductSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->filename = base_path().'/database/seeders/csvs/products.csv';
        $this->table = 'products';
        $this->connection = 'mysql';
        $this->timestamps = true;
        $this->created_at = Carbon::now()->toDateTimeString();
        $this->updated_at = Carbon::now()->toDateTimeString();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        parent::run();
    }
}
