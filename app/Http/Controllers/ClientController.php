<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Gate;


class ClientController extends Controller
{

    public function index(){

        try {

            // using package buildder
            $products = QueryBuilder::for(Client::class)
            ->allowedIncludes(['user']) // allowing relation
            ->with('user') // adding it relation as many times
            ->allowedFilters(['name', 'email', 'phone'])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at','updated_at'])
            ->paginate(100);

            return response()->json([
                'status' => 'success',
                'message' => 'Client queried successfull',
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

            $client = QueryBuilder::for(Client::class)->where('id', $id)
            ->allowedIncludes(['user']) // allowing relation
            //->with('images') // adding it relation as many times
            ->with('user')
            ->first();


            if(!$client){
                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Client not found 404',
                    'data'=> null
                ], 404);
            }

            return response()->json([
                'status'=> 'success',
                'message'=> 'Client created successfull',
                'data'=> $client
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
                'email' => 'required|string',
                'phone' => 'required|numeric',
                'alternate_phone' => 'required|numeric',
                'address' => 'required|string',
            ]);


            // new instance of product
            $client = new Client;

            $client->user_id = $request->user_id;
            $client->name = $request->name;
            $client->email = $request->email;
            $client->phone = $request->phone;
            $client->alternate_phone = $request->alternate_phone;
            $client->address = $request->address;

            $client->save();


            return response()->json([
                'status'=> 'success',
                'message'=> 'Client created successfully',
                'data' => $client
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
                'email' => 'required|string',
                'phone' => 'required|numeric',
                'alternate_phone' => 'required|numeric',
                'address' => 'required|string',
            ]);

            // find product by passed id
            $client = QueryBuilder::for(Client::class)->where('id', $id)->first();

            // check availability of product
            if(!$client){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'product not found 404',
                    'data'=> null
                ], 404);

            }

            // Authorisatin check
            if (!Gate::allows('authorized-user-client', $client)) {

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Authorisation failed! You are not allowed to perform this actions',
                    // 'data'=> null
                ], 403);

            }

            $client->user_id = $request->user_id;
            $client->name = $request->name;
            $client->email = $request->email;
            $client->phone = $request->phone;
            $client->alternate_phone = $request->alternate_phone;
            $client->address = $request->address;

            $client->save();


            return response()->json([
                'status'=> 'success',
                'message'=> 'Client updated successfully',
                'data'=> $client
            ], 200);


        }catch(\Exception $e){

            return response()->json([
                'status'=> 'faile',
                'message'=> $e->getMessage(),
                'data'=> null
            ], 500);

        }
    }

    public function deleteProduct($client_id){

        try{

            // find image
            $client = QueryBuilder::for(Client::class)->where('id', $client_id)->first();

            // check image
            if(!$client){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Client not found 404',
                    'data'=> null
                ], 404);

            }

            // Authorisatin check
            if (!Gate::allows('authorized-user-client', $client)) {

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Authorisation failed! You are not allowed to perform this actions',
                    // 'data'=> null
                ], 403);

            }

            // delete the product its self
            $client->delete();


            return response()->json([
                'status'=> 'success',
                'message' => 'Client deleted successfull',
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
