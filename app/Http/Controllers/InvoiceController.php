<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatusEnum;
use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Jobs\SendInvoiceJob;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller  {


    public function index(){

        try{

            $invoices = QueryBuilder::for(Invoice::class)
           ->allowedIncludes(['invoice_items', 'user']) // allowing relation
            ->with('user') // adding it relation as many times
            ->with('invoice_items.product') // adding it relation as many times
            ->allowedFilters(['invoice_id', 'user_id', 'invoice_date', 'client_name', 'client_phone', 'status'])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at','updated_at'])
            ->paginate(100);



            return response()->json([
                'status' => 'success',
                'messages' => 'Invoice queried successfully',
                'data' => $invoices
            ], 200);

        }catch(\Exception $e){

            return response()->json([
                'status' => 'failed',
                'messages' =>  $e->getMessage(),
                'data' => null
            ], 500);

        }
    }

    public function showInvoice($invoice_id){

        try{

            $invoice = QueryBuilder::for(Invoice::class)->where('id', $invoice_id)
            ->allowedIncludes(['invoice_items', 'user']) // allowing relation
            ->with('user') // adding it relation as many times
            ->with('invoice_items.product')
            ->first();

            if(!$invoice){

                return response()->json([
                    'status' => 'failed',
                    'messages' => 'Invoice cannot be found !',
                    'data' => null
                ], 404);

            }

            return response()->json([
                'status' => 'success',
                'messages' => 'Invoice Queried successfully',
                'data' => $invoice
            ], 200);

        }catch(\Exception $e){

            return response()->json([
                'status' => 'failed',
                'messages' => $e->getMessage(),
                'data' => null
            ], 500);

        }
    }

    public function createInvoice(CreateInvoiceRequest $request){

        try{

            // validate incoming reques
            $validated = $request->validated();

            // generate UUid for invoice_item
            $invoice_uuid = Uuid::uuid4();

            // create array or items
            $invoice_items_array = array();

            // instanciate total_amount & ..
            $total_amount = 0.0;
            $invoice_item_total_price = 0.0;


            // we find the client we sending the invoice
            // $client = QueryBuilder::for(Client::class)->where('id', $validated['client_id'])->firts();



            $invoice = Invoice::create([
                'invoice_id' => $invoice_uuid,
                'user_id' => $validated['user_id'],
                'invoice_date' => Carbon::now(),
                'client_id' => $validated['client_id'],
                'client_address' => $validated['client_address'],
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'client_phone' => $validated['client_phone'],
                'delivery_amount' => $validated['delivery_amount'],
                'client_alternate_phone' => $validated['client_alternate_phone'],
                'total_amount' => $total_amount,
                'additional_information' => $validated['additional_information'],
                'status' => InvoiceStatusEnum::PROCESSING,
            ]);



            foreach($validated['invoice_items'] as $invoice_item){
                // add the total price of each invoice item
                $invoice_item_total_price = $invoice_item['price'] * $invoice_item['quantity'];

                // add it to the total_amout
                $total_amount += $invoice_item_total_price;

                // store the invoice_item in db
                $new_invoice_item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $invoice_item['product_id'],
                    'quantity' => $invoice_item['quantity'],
                    'total' => $invoice_item_total_price,
                    'price' => $invoice_item['price'],
                ]);

                // push invoice_items in the array
                array_push($invoice_items_array, $new_invoice_item);

            }

            // add the set delivery to the total
            $total_amount += $validated['delivery_amount'];

            // since the total amount is created later, then we have to update the invoice
            $updated_invoice = QueryBuilder::for(Invoice::class)->where('id', $invoice->id)
            ->allowedIncludes(['invoice_items', 'user']) // allowing relation
            ->with('user') // adding it relation as many times
            ->with('invoice_items.product')
            ->first();

            $updated_invoice->total_amount = $total_amount;

            $updated_invoice->save();

            // get the user(organisation)
            // $user = QueryBuilder::for(User::class)->where('id', $invoice->user_id)->first();


            // generate download link
            $url =  URL::signedRoute('generateInvoicePDF', ['invoice_id' => $updated_invoice->id]);

            // generate subject
            $emailSubject = 'Your Invoice is ready';
            // send email notification to user
            SendInvoiceJob::dispatch($updated_invoice, $url, $emailSubject);

            return response()->json([
                'status'=> 'success',
                'message' => 'Invoice created successfully',
                // 'total_amount' => $total_amount,
                'data' => $updated_invoice,
            ], 200);

        }catch(\Exception $e){

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }

    }


    public function updateInvoice(Request $request, $invoice_id ){

        try{

            $request->validate([
                // 'name' => 'required',
                   'client_id' => 'required|numeric|exists:clients,id',
                    'client_name' => 'required|string',
                    'client_address' => 'required|string',
                    'client_email' => 'required|string',
                    'client_phone' => 'required|string',
                    'client_alternate_phone' => 'sometimes|string' ,
                    // 'total_amount' => 'required|numeric',
                    'additional_information' => 'sometimes|string',
                    'delivery_amount' => 'required|numeric',
                    // 'status' => 'required|string',
                    'invoice_items' => 'required|array',
                    'invoice_items.*.product_id' => 'required|numeric|exists:products,id',
                    'invoice_items.*.quantity' => 'required|numeric',
                    'invoice_items.*.price' => 'required|numeric'
              ]);



            // instanciate total_amount & ..
            $total_amount = 0.0;
            $invoice_item_total_price = 0.0;

            // create array or items
            $invoice_items_array = array();

             //find invoice
            //  $invoice =  QueryBuilder::for(Invoice::class)->where('id', $invoice_id)->first();
             $invoice = QueryBuilder::for(Invoice::class)->where('id', $invoice_id)
             ->allowedIncludes(['invoice_items', 'user']) // allowing relation
             ->with('user') // adding it relation as many times
             ->with('invoice_items.product')
             ->first();

             if(!$invoice){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> "Invoice cannot be found",
                    'data'=> [
                        'invoice' => null,
                        'invoice_items' => null
                    ]
                ], 404);

             }

             // authorise checked
            if (!Gate::allows('authorized-user-invoice', $invoice)) {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Authorization failed! You are not authorized to perform this action',
                    'data' => null
                ], 403);

            }

            // check if invoice_items_request is not empty
            if(!empty($request->invoice_items)){

                // Extract invoice item IDs from the request
                // $invoiceItemIds = array_column($request->invoice_items, 'id');

                // find the invoice_items attached to the invoice
                $invoice_items =  QueryBuilder::for(InvoiceItem::class)->where('invoice_id', $invoice->id)->get();

                // delete the old invoice items
                foreach($invoice_items as $invoiceItem){
                    $invoiceItem->delete();
                }

                // add new invoice items
                foreach($request->invoice_items as $invoice_item){
                    // add the total price of each invoice item
                    $invoice_item_total_price = $invoice_item['price'] * $invoice_item['quantity'];

                    // add it to the total_amout
                    $total_amount += $invoice_item_total_price;

                    // update or store the invoice_item in db
                    $new_invoice_item = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $invoice_item['product_id'],
                        'quantity' => $invoice_item['quantity'],
                        'total' => $invoice_item_total_price,
                        'price' => $invoice_item['price'],
                    ]);

                    // push invoice_items in the array
                    array_push($invoice_items_array, $new_invoice_item);

                    }

            }

            //add the set delivery to the total
            $total_amount += $request->delivery_amount;

             //  update invoice
             // $invoice->invoice_id = $invoice_uuid;
             // $invoice->user_id = $request->user_id;
             // $invoice->invoice_date = Carbon::now();
                $invoice->client_id = $request->client_id;
                $invoice->client_address = $request->client_address;
                $invoice->client_name = $request->client_name;
                $invoice->client_email = $request->client_email;
                $invoice->client_phone = $request->client_phone;
                $invoice->delivery_amount = $request->delivery_amount;
                $invoice->client_alternate_phone = $request->client_alternate_phone;
                $invoice->total_amount = $total_amount;
                $invoice->additional_information = $request->additional_information;

                $invoice->save();


                            // generate download link
                $url =  URL::signedRoute('generateInvoicePDF', ['invoice_id' => $invoice->id]);

                // generate subject
                $emailSubject = 'Your Invoice has been updated';
                // send email notification to user
                SendInvoiceJob::dispatch($invoice, $url, $emailSubject);


                return response()->json([
                    'status'=> 'success',
                    'message'=> "Updated successful",
                    'data'=> [
                        'invoice' => $invoice,
                        'invoice_items' => $invoice_items_array
                    ]
                ], 500);

        }catch(\Exception $e){

                return response()->json([
                    'status'=> 'failed',
                    'message'=> $e->getMessage(),
                    'data'=> null
                ], 500);

        }

    }



    // update ivoice status
    public function updateInvoiceStatus(Request $request, $invoice_id){

        try{

            $request->validate([
                'status' => ['required', new Enum(InvoiceStatusEnum::class)],
                // 'order_status' => ['required', new EnumValue(['pending', 'processing', 'shipped', 'delivered'])],

            ]);

            $invoice =  QueryBuilder::for(Invoice::class)->where('id', $invoice_id)->first();

            if(!$invoice){

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invoice cannot be found',
                    'data' => null
                ], 404);

            }

            // authorise checked
            if (!Gate::allows('authorized-user-invoice', $invoice)) {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Authorization failed! You are not authorized to perform this action',
                    'data' => null
                ], 403);

            }

            // update invoice
            $invoice->status = $request->status;
            $invoice->save();

            // find the invoice

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice status updated successfully',
                'data' => $invoice
            ], 200);


        }catch(\Exception $e){

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);


        }
    }


    public function generateInvoicePDF(Request $request, $invoice_id){

        if (!$request->hasValidSignature()) {
            // abort(401);
            return view('emails.verify.email-verified-error');
        }

        $invoice = QueryBuilder::for(Invoice::class)->where('id', $invoice_id)
        ->allowedIncludes(['invoice_items', 'user']) // allowing relation
        ->with('user') // adding it relation as many times
        ->with('invoice_items.product')
        ->first();

        $test = $invoice->invoice_id;

        $pdf = Pdf::loadView('invoice.invoicepdf', ['invoice' => $invoice]);
        return $pdf->download();

        // return $updated_invoice;
    }
}
