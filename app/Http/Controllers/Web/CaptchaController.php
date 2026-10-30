<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Captcha;

class CaptchaController extends Controller
{
    /**
     * Serve a fresh captcha image for the contact form.
     */
    public function contact()
    {
        return response((new Captcha('contact'))->image(), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
