<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;
use Throwable;


class ProductImport implements ToCollection, SkipsOnError, WithValidation, WithBatchInserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use Importable, SkipsErrors;


    public function collection(collection $collection)
    {

        foreach ($collection as $key => $value) {
            if ($key>0) {


                //Product Save
                $product = DB::table('products')->insert([
                    'category_id' => $value[0],
                    'sub_category_id' => $value[1],
                    'pro_sub_category_id' => $value[2],
                    'pro_pro_category_id' => $value[3],
                    'component_id' => $value[4],
                    'name' => $value[5], 
                    'slug' => $value[6], 
                    'discount_price' => $value[7],
                    'regular_price' => $value[8],
                    'special_price' => $value[9],
                    'call_for_price' => $value[10],
                    'warranty' => $value[11],
                    'discount' => $value[12],
                    'qty' => $value[13],
                    'status' => $value[14],
                    'image' => $value[15],
                    'user_id' => 0,
                    'supplier_id' => 0,
                ]);

                $product_id = DB::getPdo()->lastInsertId();

                //Product Brand Save
                $product_brands = explode(',', $value[16]);
                foreach ($product_brands as $key => $brand) {
                    $product_brand = DB::table('product_brands')->insert([
                        'product_id' => $product_id,
                        'brand_id' => $brand,

                    ]);
                }


                //Product More Image Save
                $product_more_images = explode(',', $value[17]);
                foreach ($product_more_images as $key => $image) {
                    $product_more_image = DB::table('product_images')->insert([
                        'product_id' => $product_id,
                        'product_image' => $image,

                    ]);
                }

                //Product EMI Save
                $product_emis = explode(',', $value[18]);
                foreach ($product_emis as $key => $emi) {
                    $emi_explode = explode('-', $emi);
                    $product_emi = DB::table('product_emis')->insert([
                        'product_id' => $product_id,
                        'emi_month' => $emi_explode[0],
                        'emi_price' => $emi_explode[1],

                    ]);
                }


                //Product Shop Save
                $product_shops = explode(',', $value[19]);
                foreach ($product_shops as $key => $shop) {
                    $product_shop = DB::table('product_shops')->insert([
                        'product_id' => $product_id,
                        'shop_type_id' => $shop,

                    ]);
                }

            }

        }


    }


    public function rules(): array
    {
        return [
            '6' => 'unique:products,slug'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '6.unique' => 'You Slug Already Exist !! Please Check Your File Again!!',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    // public function chunkSize(): int
    // {
    //     return 1000;
    // }


}
