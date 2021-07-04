<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class SearchController extends Controller
{
 
    public function search(Request $request)
    {
        $keyword = $request->keyword;
         $lat = $request->lat;
       $lng = $request->lng;
       
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                //   ->where('store.city',$city)
                  ->orderBy('distance')
                  ->first();
    if($nearbystore->del_range >= $nearbystore->distance)  { 
        $prod = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
			     ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
			     ->select('product.product_name','product.product_id')
                 ->groupBy('product.product_name','product.product_id')
                 ->where('store_products.store_id', $nearbystore->store_id)
                ->where('product.product_name', 'like', '%'.$keyword.'%')
                ->get();

        if(count($prod)>0){
            $result =array();
            $i = 0;

            foreach ($prod as $prods) {
                array_push($result, $prods);

                $app = json_decode($prods->product_id);
                $apps = array($app);
                $app = DB::table('store_products')
					   ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                     ->Leftjoin('deal_product','product_varient.varient_id','=','deal_product.varient_id')
                         ->select('store_products.store_id','store_products.stock','product_varient.varient_id', 'product_varient.description', 'store_products.price', 'store_products.mrp', 'product_varient.varient_image','product_varient.unit','product_varient.quantity','deal_product.deal_price', 'deal_product.valid_from', 'deal_product.valid_to')
                         ->where('store_products.store_id', $nearbystore->store_id)
                        ->whereIn('product_varient.product_id', $apps)
                        ->get();
                        
                $result[$i]->varients = $app;
                $i++; 
             
            }

            $message = array('status'=>'1', 'message'=>'Products found', 'data'=>$prod);
            return $message;
        }
        else{
            $message = array('status'=>'0', 'message'=>'Products not found', 'data'=>[]);
            return $message;
        }
      }
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
    }
}
