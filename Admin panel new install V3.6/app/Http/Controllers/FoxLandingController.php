<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Models\Contact;
use App\Models\DataSetting;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class FoxLandingController extends Controller
{
    public function home()
    {
        return view('fox-landing.home', $this->sharedData());
    }

    public function about()
    {
        return view('fox-landing.about', $this->sharedData());
    }

    public function contact()
    {
        return view('fox-landing.contact', $this->sharedData());
    }

    public function storeRegistration()
    {
        return view('fox-landing.store-registration', $this->sharedData());
    }

    public function deliveryRegistration()
    {
        return view('fox-landing.delivery-registration', $this->sharedData());
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'message' => 'required|string|max:5000',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->subject = 'Contato Fox Landing';
        $contact->message = $request->message;
        $contact->save();

        Toastr::success('Mensagem enviada com sucesso!');
        return redirect()->route('fox.contact');
    }

    private function sharedData(): array
    {
        $landingPageLinks = DataSetting::where(['type' => 'admin_landing_page', 'key' => 'landing_page_links'])->value('value');
        $downloadLinks = DataSetting::where(['type' => 'admin_landing_page', 'key' => 'download_user_app_links'])->value('value');

        $landingPageLinks = is_string($landingPageLinks) ? json_decode($landingPageLinks, true) : [];
        $downloadLinks = is_string($downloadLinks) ? json_decode($downloadLinks, true) : [];

        return [
            'businessName' => Helpers::get_business_settings('business_name') ?? 'Fox Delivery',
            'businessLogo' => Helpers::logoFullUrl(),
            'landingLinks' => is_array($landingPageLinks) ? $landingPageLinks : [],
            'downloadLinks' => is_array($downloadLinks) ? $downloadLinks : [],
            'contactPhone' => Helpers::get_business_settings('phone') ?? '(11) 9999-9999',
            'contactEmail' => Helpers::get_business_settings('email_address') ?? 'contato@foxdelivery.com.br',
            'contactAddress' => Helpers::get_business_settings('address') ?? 'Rua Oliveira 122, São Paulo - SP',
        ];
    }
}
