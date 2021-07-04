<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;

class CategoryController extends Controller
{
 public function cate(Request $request)
    {
     $cat = DB::table('categories')
          ->where('level', 0)
          ->get();

        if(count($cat)>0){
            $result =array();
            $i = 0;

            foreach ($cat as $cats) {
                array_push($result, $cats);

                $app = json_decode($cats->cat_id);
                $apps = array($app);
                $app = DB::table('categories')
                        ->whereIn('parent', $apps)
                        ->where('level',1)
                        ->get();
                        
                $result[$i]->subcategory = $app;
                $i++; 
                $res =array();
                $j = 0;
                foreach ($app as $appss) {
                    array_push($res, $appss);
                    $c = array($appss->cat_id);
                    $app1 = DB::table('categories')
                            ->whereIn('parent', $c)
                            ->where('level',2)
                            ->get();
                if(count($app1)>0){        
                    $res[$j]->subchild = $app1;
                    $j++; 
                   }
                   else{
                     $res[$j]->subchild = [];
                    $j++;  
                   }
                 }   
             
            }

            $message = array('status'=>'1', 'message'=>'data found', 'data'=>$cat);
            return $message;
        }
        else{
            $message = array('status'=>'0', 'message'=>'data not found', 'data'=>[]);
            return $message;
        }
    }
      
  public function cat_product(Request $request)
    {
     $cat_id =$request->cat_id;  
       $lat = $request->lat;
       $lng = $request->lng;
        $cityname = $request->city;
       $city = ucfirst($cityname);
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first();
 if($nearbystore){                 
    if($nearbystore->del_range >= $nearbystore->distance)  {           
       $prod =  DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
          ->where('product.cat_id', $cat_id)
          ->where('store_products.store_id', $nearbystore->store_id)
          ->where('store_products.price','!=',NULL)
          ->where('product.hide',0)
          ->get();

        if(count($prod)>0){
            $result =array();
            $i = 0;

            foreach ($prod as $prods) {
                array_push($result, $prods);

                $app = json_decode($prods->product_id);
                $apps = array($app);
                $app =  DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                 ->Leftjoin('deal_product','product_varient.varient_id','=','deal_product.varient_id')
                         ->select('store_products.store_id','store_products.stock','product_varient.varient_id', 'product_varient.description', 'store_products.price', 'store_products.mrp', 'product_varient.varient_image','product_varient.unit','product_varient.quantity','deal_product.deal_price', 'deal_product.valid_from', 'deal_product.valid_to')
                         ->where('store_products.store_id', $nearbystore->store_id)
                        ->whereIn('product_varient.product_id', $apps)
                        ->where('store_products.price','!=',NULL)
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
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
     
    }   
    
    
    
 public function cat(Request $request)
    {
     $cat = DB::table('categories')
          ->where('level', 0)
          ->get();

        if(count($cat)>0){
            $result =array();
            $i = 0;

            foreach ($cat as $cats) {
                array_push($result, $cats);

                $app = json_decode($cats->cat_id);
                $apps = array($app);
                $app = DB::table('categories')
                        ->whereIn('parent', $apps)
                        ->where('level',1)
                        ->get();
                        
                        
                if(count($app)>0){
                $result[$i]->subcategory = $app;
                $i++; 
                $res =array();
                $j = 0;
                foreach ($app as $appss) {
                    array_push($res, $appss);
                    $c = array($appss->cat_id);
                    $app1 = DB::table('categories')
                            ->whereIn('parent', $c)
                            ->where('level',2)
                            ->get();
                if(count($app1)>0){        
                    $res[$j]->subchild = $app1;
                    $j++; 
                    $res2 =array();
                    $k = 0;
                    foreach ($app1 as $apps1) {
                        array_push($res2, $apps1);
                        $catt = array($apps1->cat_id);
                        $prod = DB::table('product')
                                ->whereIn('cat_id', $catt)
                                ->get();
                                
                     $res2[$k]->product = $prod;
                     $k++;   
                     }
                    
                   }
                   else{
                       $pr = DB::table('product')
                        ->whereIn('cat_id', $c)
                        ->get();    
                        $res[$j]->product = $pr;
                        $j++; 
                   }
                 }   
                }
                else{
                $app = DB::table('product')
                        ->whereIn('cat_id', $apps)
                        ->get();    
                $result[$i]->product = $app;
                $i++; 
                }
            }

            $message = array('status'=>'1', 'message'=>'data found', 'data'=>$cat);
            return $message;
        }
        else{
            $message = array('status'=>'0', 'message'=>'data not found', 'data'=>[]);
            return $message;
        }
    }
    
     public function varient(Request $request)
    {
        $prod_id = $request->product_id;
         $lat = $request->lat;
       $lng = $request->lng;
        $cityname = $request->city;
       $city = ucfirst($cityname);
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first();
if($nearbystore){                  
    if($nearbystore->del_range >= $nearbystore->distance)  {                
        $varient= DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                 ->Leftjoin('deal_product','product_varient.varient_id','=','deal_product.varient_id')
                         ->select('store_products.store_id','store_products.stock','product_varient.varient_id', 'product_varient.description', 'store_products.price', 'store_products.mrp', 'product_varient.varient_image','product_varient.unit','product_varient.quantity','deal_product.deal_price', 'deal_product.valid_from', 'deal_product.valid_to')
                ->where('product_id',$prod_id)
                ->where('store_products.price','!=',NULL)
                ->where('store_products.store_id',$nearbystore->store_id)
                ->get();
        if(count($varient)>0){        
          $message = array('status'=>'1', 'message'=>'varients', 'data'=>$varient);
            return $message;
        }
        else{
            $message = array('status'=>'0', 'message'=>'data not found', 'data'=>[]);
            return $message;
        } 
    }
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
        
       }
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
                
    }
    
    
     public function dealproduct(Request $request)
    {
        $d = Carbon::Now();
       $lat = $request->lat;
       $lng = $request->lng;
       $cityname = $request->city;
       $city = ucfirst($cityname);
       
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first();
    if($nearbystore){  
       if($nearbystore->del_range >= $nearbystore->distance)  {        
        $deal_p= DB::table('deal_product')
                ->join('store_products', 'deal_product.varient_id', '=', 'store_products.varient_id')
                ->join('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                ->join('product', 'product_varient.product_id', '=', 'product.product_id')
                ->select('store_products.store_id','store_products.stock','deal_product.deal_price as price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->groupBy('store_products.store_id','store_products.stock','deal_product.deal_price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->where('store_products.store_id',$nearbystore->store_id)
                ->whereDate('deal_product.valid_from','<=',$d->toDateString())
                ->WhereDate('deal_product.valid_to','>',$d->toDateString())
                ->where('store_products.price','!=',NULL)
                ->where('product.hide',0)
                ->get();
          
          
          if(count($deal_p)>0){
            $result =array();
            $i = 0;
             $j = 0;
            foreach ($deal_p as $deal_ps) {
                array_push($result, $deal_ps);
                
                $val_to =  $deal_ps->valid_to;       
                $diff_in_minutes = $d->diffInMinutes($val_to); 
                $totalDuration =  $d->diff($val_to)->format('%H:%I:%S');
                $result[$i]->timediff = $diff_in_minutes;
                $i++; 
                $result[$j]->hoursmin= $totalDuration;
                $j++; 
            }

            $message = array('status'=>'1', 'message'=>'Products found', 'data'=>$deal_p);
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
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }

    }
    
    
       public function top_six(Request $request){
          $lat = $request->lat;
       $lng = $request->lng;
        $cityname = $request->city;
       $city = ucfirst($cityname);
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first(); 
if($nearbystore){  
    if($nearbystore->del_range >= $nearbystore->distance)  {                 
      $topsix = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
                  ->Leftjoin ('store_orders', 'product_varient.varient_id', '=', 'store_orders.varient_id') 
                  ->Leftjoin ('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
                  ->join ('categories', 'product.cat_id','=','categories.cat_id')
                  ->select('categories.title', 'categories.image', 'categories.description','categories.cat_id',DB::raw('count(store_orders.varient_id) as count'))
                  ->groupBy('categories.title', 'categories.image', 'categories.description','categories.cat_id')
                   ->where('store_products.store_id', $nearbystore->store_id)
                   ->where('store_products.price','!=',NULL)
                   ->where('product.hide',0)
                  ->orderBy('count','desc')
                  ->limit(6)
                  ->get();
         if(count($topsix)>0){
        	$message = array('status'=>'1', 'message'=>'Top Six Categories', 'data'=>$topsix);
        	return $message;
        }
        else{
        	$message = array('status'=>'0', 'message'=>'Nothing in Top Six', 'data'=>[]);
        	return $message;
        } 
    }
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
    } 
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
  }   
 public function homecat(Request $request){
        $lat = $request->lat;
       $lng = $request->lng;
      
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first(); 
if($nearbystore){  
    if($nearbystore->del_range >= $nearbystore->distance)  {      
     $category = DB::table('tbl_top_cat')
    	          ->join('categories','tbl_top_cat.cat_id','=','categories.cat_id')
    	          ->join ('product', 'categories.cat_id', '=', 'product.cat_id')
    	          ->join ('product_varient', 'product.product_id', '=', 'product_varient.product_id')
    	          ->join ('store_products', 'product_varient.varient_id', '=', 'store_products.varient_id')
    	          ->select('categories.cat_id','tbl_top_cat.cat_rank','categories.title','categories.image')
    	          ->groupBy('categories.cat_id','tbl_top_cat.cat_rank','categories.title','categories.image')
    	          ->where('store_products.store_id', $nearbystore->store_id)
                  ->where('store_products.price','!=',NULL)
                   ->where('product.hide',0)
                   ->orderBy('tbl_top_cat.cat_rank','ASC')
    	          ->get();
    	          
    	          
       if(count($category)>0){
        	$message = array('status'=>'1', 'message'=>'Home Categories', 'data'=>$category);
        	return $message;
        }
        else{
        	$message = array('status'=>'0', 'message'=>'Nothing in Home Category', 'data'=>[]);
        	return $message;
        } 
       }
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
        } 
       else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
 }
 
   public function homepage(Request $request)
    {  
       $d = Carbon::Now();
       $lat = $request->lat;
       $lng = $request->lng;
               $banner = DB::table('banner')
                ->get();
       $banner2 = DB::table('secondary_banner')
                ->get();
       $nearbystore = DB::table('store')
                    ->select('del_range','store_id',DB::raw("6371 * acos(cos(radians(".$lat . ")) 
                    * cos(radians(store.lat)) 
                    * cos(radians(store.lng) - radians(" . $lng . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(store.lat))) AS distance"))
                  ->where('store.del_range','>=','distance')
                  ->orderBy('distance')
                  ->first();
    if($nearbystore){  
       if($nearbystore->del_range >= $nearbystore->distance)  {        
        $deal_p= DB::table('deal_product')
                ->join('store_products', 'deal_product.varient_id', '=', 'store_products.varient_id')
                ->join('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                ->join('product', 'product_varient.product_id', '=', 'product.product_id')
                ->select('store_products.store_id','store_products.stock','deal_product.deal_price as price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->groupBy('store_products.store_id','store_products.stock','deal_product.deal_price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->where('store_products.store_id',$nearbystore->store_id)
                 ->whereDate('deal_product.valid_from','<=',$d->toDateString())
                ->where('deal_product.valid_to','>',$d->toDateString())
                ->where('store_products.price','!=',NULL)
                ->where('product.hide',0)
                ->get();
                
          $topsix = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
                  ->Leftjoin ('store_orders', 'product_varient.varient_id', '=', 'store_orders.varient_id') 
                  ->Leftjoin ('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
                  ->join ('categories', 'product.cat_id','=','categories.cat_id')
                  ->select('categories.title', 'categories.image', 'categories.description','categories.cat_id',DB::raw('count(store_orders.varient_id) as count'))
                  ->groupBy('categories.title', 'categories.image', 'categories.description','categories.cat_id')
                   ->where('store_products.store_id', $nearbystore->store_id)
                   ->where('store_products.price','!=',NULL)
                   ->where('product.hide',0)
                  ->orderBy('count','desc')
                  ->limit(6)
                  ->get();
          $top_selling = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
                  ->Leftjoin ('deal_product', 'product_varient.varient_id', '=', 'deal_product.varient_id') 
                  ->Leftjoin ('store_orders', 'store_products.varient_id', '=', 'store_orders.varient_id') 
                  ->Leftjoin ('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
                   ->select('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from',DB::raw('count(store_orders.varient_id) as count'))
                ->groupBy('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                  
                  ->where('store_products.store_id', $nearbystore->store_id)
                  ->where('deal_product.deal_price', NULL)
                  ->where('store_products.price','!=',NULL)
                  ->where('product.hide',0)
                  ->orderBy('count','desc')
                  ->limit(10)
                  ->get();
                  
            $new = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
                  ->Leftjoin ('store_orders', 'store_products.varient_id', '=', 'store_orders.varient_id') 
                  ->Leftjoin ('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
                  ->Leftjoin ('deal_product', 'product_varient.varient_id', '=', 'deal_product.varient_id')
                   ->select('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->groupBy('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                   
                  
                   ->where('store_products.store_id', $nearbystore->store_id)
                  ->where('deal_product.deal_price', NULL)
                ->where('store_products.price','!=',NULL)
                ->where('product.hide',0)
                  ->orderByRaw('RAND()')
                  ->limit(10)
                  ->get();
                  
             $recentselling = DB::table('store_products')
                 ->join ('product_varient', 'store_products.varient_id', '=', 'product_varient.varient_id')
                  ->join ('product', 'product_varient.product_id', '=', 'product.product_id')
                  ->Leftjoin ('deal_product', 'product_varient.varient_id', '=', 'deal_product.varient_id') 
                  ->Leftjoin ('store_orders', 'store_products.varient_id', '=', 'store_orders.varient_id') 
                  ->Leftjoin ('orders', 'store_orders.order_cart_id', '=', 'orders.cart_id')
                   ->select('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                ->groupBy('store_products.store_id','store_products.stock','store_products.price', 'product_varient.varient_image', 'product_varient.quantity','product_varient.unit', 'store_products.mrp','product_varient.description' ,'product.product_name','product.product_image','product_varient.varient_id','product.product_id','deal_product.valid_to','deal_product.valid_from')
                   ->where('store_products.store_id', $nearbystore->store_id)
                  ->orderByRaw('RAND()')
                  ->where('deal_product.deal_price', NULL)
                  ->where('product.hide',0)
                ->where('store_products.price','!=',NULL)
                  ->limit(10)
                  ->get();            
          
          if(count($deal_p)>0){
            $result =array();
            $i = 0;
             $j = 0;
            foreach ($deal_p as $deal_ps) {
                array_push($result, $deal_ps);
                
                $val_to =  $deal_ps->valid_to;       
                $diff_in_minutes = $d->diffInMinutes($val_to); 
                $totalDuration =  $d->diff($val_to)->format('%H:%I:%S');
                $result[$i]->timediff = $diff_in_minutes;
                $i++; 
                $result[$j]->hoursmin= $totalDuration;
                $j++; 
            }
            
          }
          if(count($top_selling)>0){
            $result1 =array();
            
            $k = 0;
             $l = 0;
            foreach ($top_selling as $deal_pss) {
                array_push($result1, $deal_pss);
                $result1[$k]->timediff = NULL;
                $k++;
                $result1[$l]->hoursmin= NULL;
                $l++;
            }
          }
          if(count($recentselling)>0){
            $result2 =array();
            $m = 0;
             $n = 0;
            foreach ($recentselling as $deal_ps) {
                array_push($result2, $deal_ps);
                $result2[$m]->timediff = NULL;
                $m++; 
                $result2[$n]->hoursmin= NULL;
                $n++; 
            }
          }
          if(count($new)>0){
            $result3 =array();
            $o = 0;
             $p = 0;
            foreach ($new as $deal_ps) {
                array_push($result3, $deal_ps);
                $result3[$o]->timediff = NULL;
                $o++; 
                $result3[$p]->hoursmin= NULL;
                $p++; 
            }
          }
            $message = array('status'=>'1', 'message'=>'homepage data', 'banner1'=>$banner,'banner2'=>$banner2, 'top_selling'=>$top_selling,'recentselling'=>$recentselling,'whats_new'=>$new ,'deal_products'=>$deal_p,'top_category'=>$topsix,);
            return $message;
       }
         else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
    }
     else{
           $message = array('status'=>'2', 'message'=>'No Products Found Nearby', 'data'=>[]);
            return $message; 
       }
    }
    
}