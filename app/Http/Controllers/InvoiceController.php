<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Classes\Seller;
use LaravelDaily\Invoices\Invoice;

class InvoiceController extends Controller
{
    public function download(Reservation $reservation)
    {
        $tenant = $reservation->tenant;

        App::setLocale('mk');
        $seller = new Party([
            'name'          => $tenant->name,
            'address'       => $tenant->address ?? 'N/A',
            'phone'         => $tenant->phone ?? 'N/A',
            'custom_fields' => [
                'email'     => $tenant->email ?? 'N/A',
                'website'   => $tenant->website ?? 'N/A',
            ],
        ]);

        $customer = new Buyer([
            'name'          => $reservation->client->name,
            'custom_fields' => [
                'Телефон' => $reservation->client->telephone ?? 'N/A',
                'email' => $reservation->client->email,
            ],
        ]);

        $items = [];

        foreach ($reservation->operations as $operation) {
            $items[] = InvoiceItem::make($operation->name)
                ->pricePerUnit($operation->price);
        }

        $invoice = Invoice::make()
            ->seller($seller)
            ->buyer($customer)
            ->addItems($items);

        return $invoice->stream();
    }
}
