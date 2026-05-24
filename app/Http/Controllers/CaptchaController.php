<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    public function generate()
    {
        if (ob_get_contents()) {
            ob_end_clean();
        }

        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Sincronización exacta con el validador del paquete original
        cache(['captcha_' . session()->getId() => strtolower($code)], 300);

        $image = imagecreatetruecolor(140, 42);
        $bgColor = imagecolorallocate($image, 243, 244, 246);
        imagefill($image, 0, 0, $bgColor);

        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imageline($image, rand(0, 140), rand(0, 42), rand(0, 140), rand(0, 42), $lineColor);
        }

        for ($i = 0; $i < strlen($code); $i++) {
            $textColor = imagecolorallocate($image, rand(20, 100), rand(20, 100), rand(20, 100));
            imagestring($image, 5, 15 + ($i * 22), 12, $code[$i], $textColor);
        }

        header('Cache-Control: no-press, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-type: image/png');
        
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}