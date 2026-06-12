<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

final class ContactController extends Controller
{
    public function __invoke(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        Mail::mailer((string) config('contact.mailer'))
            ->to((string) config('contact.to'))
            ->send(new ContactMessageMail(
                senderName: $data['name'],
                senderEmail: $data['email'],
                subjectLine: $data['subject'] ?? null,
                body: $data['message'],
            ));

        return response()->json(['message' => 'ok']);
    }
}
