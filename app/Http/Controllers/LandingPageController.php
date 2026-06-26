<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactInquiryRequest;
use App\Mail\LandingContactMailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class LandingPageController extends Controller
{
    private const SUPPORTED_LANGUAGES = ['en', 'id'];

    private const PLAN_LABELS = [
        'starter' => 'Starter - IDR 100.000/mo',
        'professional' => 'Professional - IDR 175.000/mo',
        'enterprise' => 'Enterprise - custom',
        'general' => 'General inquiry',
    ];

    /**
     * Show the public SaaS landing page to guests, or redirect
     * authenticated users straight into the application dashboard.
     */
    public function __invoke(Request $request)
    {
        if (Auth::check()) {
            return redirect('/home');
        }

        return $this->renderLanding($request);
    }

    /**
     * Handle contact form submission from the landing page.
     */
    public function contact(ContactInquiryRequest $request): JsonResponse
    {
        return $this->dispatchInquiry($request, 'contact');
    }

    /**
     * Handle self-registration request from the landing page pricing section.
     */
    public function register(ContactInquiryRequest $request): JsonResponse
    {
        return $this->dispatchInquiry($request, 'register');
    }

    private function dispatchInquiry(ContactInquiryRequest $request, string $type): JsonResponse
    {
        $payload = $request->safe()->except(['consent', 'hp_field']);
        $payload['type'] = $type;
        $payload['plan_label'] = self::PLAN_LABELS[$payload['plan'] ?? 'general'] ?? 'General inquiry';
        $payload['ip'] = $request->ip();
        $payload['sent_at'] = now()->toDateTimeString();
        $payload['subject'] = $payload['subject'] ?? ($type === 'register'
            ? 'Registration request - '.($payload['plan_label'] ?? 'Plan')
            : 'Contact inquiry');

        $this->ensureRateLimit($request, $type);

        try {
            Mail::send(new LandingContactMailable($payload));
        } catch (\Throwable $e) {
            Log::error('Landing contact email failed', ['exception' => $e->getMessage(), 'type' => $type]);

            return response()->json([
                'success' => false,
                'message' => 'We could not send your message right now. Please email admin@rekayasadigital.com directly.',
            ], 500);
        }

        Log::info('Landing inquiry submitted', [
            'type' => $type,
            'plan' => $payload['plan'] ?? null,
            'ip' => $payload['ip'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $type === 'register'
                ? 'Thank you! Our team will review your registration and reach out within one business day.'
                : 'Thank you! Our team will reply to your message within one business day.',
        ]);
    }

    /**
     * Rate-limit by IP + type to slow form abuse. Allows 3 per 10 minutes
     * per (ip, type) bucket; backs off with a 429 if exceeded.
     */
    private function ensureRateLimit(Request $request, string $type): void
    {
        $key = 'landing-'.($type).'|'.$request->ip();
        $executed = RateLimiter::attempt($key, 3, function () {}, 600);

        if (! $executed) {
            abort(429, 'Too many requests. Please try again in a few minutes.');
        }
    }

    private function renderLanding(Request $request)
    {
        $language = $request->query('lang', $request->cookie('headcounter_landing_language', 'en'));
        $language = in_array($language, self::SUPPORTED_LANGUAGES, true) ? $language : 'en';

        if ($request->has('lang')) {
            Cookie::queue('headcounter_landing_language', $language, 60 * 24 * 365);
        }

        $publicUrl = rtrim(config('app.public_url', 'https://hotel.rekayasadigital.com'), '/');

        return view('landing', [
            'appName' => config('app.name', 'Head Counter'),
            'canonicalUrl' => $publicUrl.'/',
            'contactEmail' => 'admin@rekayasadigital.com',
            'language' => $language,
            'languageAlternates' => [
                'en' => $publicUrl.'/?lang=en',
                'id' => $publicUrl.'/?lang=id',
                'x-default' => $publicUrl.'/',
            ],
            'publicUrl' => $publicUrl,
        ]);
    }
}
