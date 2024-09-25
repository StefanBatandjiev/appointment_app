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

        App::setLocale('mk');
        $seller = new Party([
            'name'          => 'Company',
            'address'       => 'Adresa 123',
            'phone'         => '070700700',
            'custom_fields' => [
                'email'     => 'contact@company.com',
                'website'   => 'https://company-website.com',
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
