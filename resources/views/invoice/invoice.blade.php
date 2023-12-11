<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verification</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Maven+Pro&display=swap');
        body{font-family: 'Maven Pro', sans-serif; background-color: #f12369}
        hr{color: #0000004f;margin-top: 5px;margin-bottom: 5px}.add td{color: #c5c4c4;text-transform: uppercase;font-size: 12px}.content{font-size: 14px}
        #download-btn{background-color: #f12369; color: white}
    </style>
</head>
<body>

    <div class="container mt-5 mb-3">
        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <div class="card px-3">
                    <div class="d-flex flex-row p-2 flex-wrap justify-content-between align-items-center">
                        {{-- logo  --}}
                         <div class="d-flex flex-row justify-content-between">
                            <img src="https://i.imgur.com/vzlPPh3.png" width="48">
                            <div class="d-flex flex-column"> <span class="font-weight-bold">Invoice</span> <small>INV-{{$invoice->invoice_id}}</small> </div>
                         </div>
                    </div>
                    <hr>
                    <h4>Hello, {{$invoice->client_name}}</h4>
                    <p>{{$emailSubject}}, click the button below to download yout invoice. Thank you</p>
                    <hr>
                    <div class="d-flex">
                         {{-- download button --}}
                         <a type="button" href="{{url($url)}}" id="download-btn" class="btn btn-sm mb-3">Download invoice</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</head>
<body>

</body>
</html>
