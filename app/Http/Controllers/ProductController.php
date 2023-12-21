<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;



class ProductController extends Controller
{

    public function index(){

        try {

            // using package buildder
            $products = QueryBuilder::for(Product::class)
            ->allowedIncludes(['user']) // allowing relation
            ->with('user') // adding it relation as many times
            ->allowedFilters(['name', 'user_id', 'price'])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at','updated_at'])
            ->paginate(100);

            return response()->json([
                'status' => 'success',
                'message' => 'Products queried successfull',
                'data' => $products
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);

        }
    }

    public function showProduct($id){
        try {

            // $product = Product::where($request->id)->first();

            $product = QueryBuilder::for(Product::class)->where('id', $id)
            ->allowedIncludes(['user']) // allowing relation
            ->with('user')
            ->first();

            if (!Gate::allows('authorized-user-product', $product)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Authorization failed! You are not authorized to perform this action',
                    'data' => null
                ], 403);
            }


            if(!$product){
                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'product not found 404',
                    'data'=> null
                ], 404);
            }

            return response()->json([
                'status'=> 'success',
                'message'=> 'product created successfull',
                'data'=> $product
            ], 200);

         }catch(\Exception $e) {
            return response()->json([
                'status'=> 'success',
                'message'=> $e->getMessage(),
                'data'=> null
            ], 200);
         }
    }

    public function createProduct(Request $request){
        try {

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'image' => 'required|image|mimes:jpeg,png,gif|max:5120',
            ]);

            $name = str()->uuid(). '.' . $request->image->getClientOriginalExtension();
            $destination_path = 'images/products';

            // store image
            $path = $request->image->storeAs($destination_path, $name);

            // new instance of product
            $product = new Product;

            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->user_id = $request->user_id;
            $product->image = $path;

            $product->save();


            return response()->json([
                'status'=> 'success',
                'message'=> 'Product created successfully',
                'data' => $product
            ], 200);

        }catch(\Exception $e) {

            return response()->json([
                'status'=> 'failed',
                'message'=> $e->getMessage(),
                'data' => null
            ], 500);

        }
    }

    public function updateProduct(Request $request, $id,){

        try {

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'image' => 'image|mimes:jpeg,png,gif|max:5120',
            ]);

            // find product by passed id
            $product = QueryBuilder::for(Product::class)->where('id', $id)->first();

            // check availability of product
            if(!$product){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'product not found 404',
                    'data'=> null
                ], 404);

            }

            // Authorisatin check
            if (!Gate::allows('authorized-user-product', $product)) {

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Authorisation failed! You are not allowed to perform this actions',
                    // 'data'=> null
                ], 403);

            }


            // check if request has image
            if($request->hasFile('image')){

                // delete old the image
                $old_file_path = $product->image;
                Storage::delete($old_file_path);

                // replace with new image
                $new_image = $request->file('image');
                $name = str()->uuid(). '.' . $new_image->getClientOriginalExtension();
                $destination_path = 'images/products';
                // store file
                $new_file_path = $new_image->storeAs($destination_path, $name);

                $product->image = $new_file_path;

            }


                $product->name =  $request->name;
                $product->description =  $request->description;
                $product->price = $request->price;
                $product->save();

                return response()->json([
                    'status'=> 'success',
                    'message'=> 'Product updated successfully',
                    'data'=> $product
                ], 200);


        }catch(\Exception $e){

            return response()->json([
                'status'=> 'faile',
                'message'=> $e->getMessage(),
                'data'=> null
            ], 500);

        }
    }

        public function deleteProduct($product_id, Request $request){

        try{

            // find image
            $product = QueryBuilder::for(Product::class)->where('id', $product_id)->first();

            // check image
            if(!$product){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'product not found 404',
                    'data'=> null
                ], 404);

            }

            // Authorisatin check
            if (!Gate::allows('authorized-user-product', $product)) {

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Authorisation failed! You are not allowed to perform this actions',
                    // 'data'=> null
                ], 403);

            }
            // delete product image
            Storage::delete($product->image);

            // delete the product its self
            $product->delete();


            return response()->json([
                'status'=> 'success',
                'message' => 'Product deleted successfull',
            ], 200);



        }catch(\Exception $e){

            return response()->json([
                'status'=> 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);

        }
    }


}
