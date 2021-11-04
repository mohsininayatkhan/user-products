<?php
namespace Database\Seeders;

use Flynsarmy\CsvSeeder\CsvSeeder;
use Carbon\Carbon;

class PurchaseSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->filename = base_path().'/database/seeders/csvs/purchased.csv';
        $this->table = 'product_user';
        //$this->connection = 'mysql';
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

