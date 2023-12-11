<?php

namespace App\Enums;



enum InvoiceStatusEnum: string
{
   case PROCESSING = "Processing";
   case SHIPPED = "Shipped";
   case DELIVERED = "Delivered";
   case CANCELLED = "Cancelled";
}

