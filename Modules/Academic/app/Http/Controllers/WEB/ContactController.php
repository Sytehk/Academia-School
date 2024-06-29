<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Services\ContactUsService;
use RealRashid\SweetAlert\Facades\Alert;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactUsService $contactService
    ) {}

    public function index($id = null)
    {
        return view('academic::contact.index', [
            'contactsMessage' => $this->contactService->getContactsMessage(),
            'id' => $id,
        ]);
    }

    public function status($id): Redirector|RedirectResponse
    {
        $this->contactService->changeMessageStatus(intval($id));

        Alert::toast('Status changed successfully.', 'success');

        return redirect('contact-message');
    }
}
