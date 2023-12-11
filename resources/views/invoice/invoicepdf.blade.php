<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Maven+Pro&display=swap'); */
        body{font-family: 'Maven Pro', sans-serif; background-color: #f12369}
        hr{color: #0000004f;margin-top: 5px;margin-bottom: 5px}.add td{color: #776d6d;text-transform: uppercase;font-size: 12px}.content{font-size: 14px}
        #download-btn{background-color: #f12369; color: white}
        .card{
            background-color: white
        }
    </style>
</head>
<body>

    <div class="container mt-5 mb-3">
        <div class="row d-flex justify-content-center">
            <div class="col-12">
                <div class="card px-3">
                    <div class="d-flex flex-row p-2 flex-wrap justify-content-between align-items-center">
                        {{-- logo  --}}
                         <div class="d-flex flex-row justify-content-between">
                            <img src="https://i.imgur.com/vzlPPh3.png" width="48">
                            <div class="d-flex flex-column"> <span class="font-weight-bold">Invoice</span>
                                {{-- {{$test}} --}}
                                {{-- <small>INV-{{$invoice->invoice_id}}</small> </div> --}}
                         </div>

                         {{-- download button --}}
                         {{-- <a type="button" href="{{url($url)}}" id="download-btn" class="btn btn-sm">Download invoice</a> --}}

                    </div>
                    <hr>
                    <div class="table-responsive p-2">
                        <table class="table table-borderless">
                            <tbody>
                                <tr class="add">
                                    <td>To</td>
                                    <td>From</td>
                                </tr>
                                <tr class="content">
                                    <td class="font-weight-bold">{{$invoice->user->organisation}} <br>email: {{$invoice->user->email}} <br>{{$invoice->user->address}}</td>
                                    <td class="font-weight-bold">{{$invoice->client_name}} <br> Attn: {{$invoice->client_email}} <br> {{$invoice->client_address}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="products p-2">
                        <table class="table table-borderless">
                            <tbody>
                                <tr class="add">
                                    <td>Product / Service</td>
                                    <td>Quantity</td>
                                    <td>Price</td>
                                    <td class="text-center">Total</td>
                                </tr>
                                @foreach($invoice->invoice_items as $invoice_item)
                                <tr class="content">
                                    <td>{{$invoice_item->product->name}}</td>
                                    <td>{{$invoice_item->quantity}}</td>
                                    <td>GHC {{$invoice_item->price}}</td>
                                    <td class="text-center text-success">GHC {{$invoice_item->total}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="products p-2">
                        <table class="table table-borderless">
                            <tbody>
                                <tr class="add">
                                    <td></td>
                                    <td>Subtotal</td>
                                    <td>Delivery</td>
                                    <td class="text-center">Total</td>
                                </tr>
                                <tr class="content">
                                    <td></td>
                                    <td>GHC {{$invoice->total_amount - $invoice->delivery_amount}}</td>
                                    <td>GHC {{$invoice->delivery_amount}}</td>
                                    <td class="text-center">GHC {{$invoice->total_amount}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    {{-- <div class="address p-2">
                        <table class="table table-borderless">
                            <tbody>
                                <tr class="add">
                                    <td>Bank Details</td>
                                </tr>
                                <tr class="content">
                                    <td> Bank Name : ADS BANK <br> Swift Code : ADS1234Q <br> Account Holder : Jelly Pepper <br> Account Number : 5454542WQR <br> </td>
                                </tr>
                            </tbody>
                        </table>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
