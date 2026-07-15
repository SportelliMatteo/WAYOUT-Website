<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function provisioningUri(string $secret, string $email): string
    {
        $issuer = (string) config('admin.otp_issuer', 'WAYOUT Admin');
        $label = $issuer.':'.$email;

        return 'otpauth://totp/'.rawurlencode($label)
            .'?secret='.rawurlencode($secret)
            .'&issuer='.rawurlencode($issuer)
            .'&algorithm=SHA1&digits=6&period=30';
    }

    public function qrSvg(string $uri): string
    {
        $renderer = new ImageRenderer(new RendererStyle(280, 2), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($uri);
    }

    public function verify(string $secret, string $code, ?int $afterStep = null, int $window = 1): ?int
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== 6) {
            return null;
        }

        $currentStep = intdiv(time(), 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $step = $currentStep + $offset;

            if (($afterStep === null || $step > $afterStep)
                && hash_equals($this->codeAtStep($secret, $step), $code)) {
                return $step;
            }
        }

        return null;
    }

    /** @return array{plain: list<string>, hashes: list<string>} */
    public function generateRecoveryCodes(int $count = 10): array
    {
        $plain = [];
        $hashes = [];

        for ($i = 0; $i < $count; $i++) {
            $raw = strtoupper(bin2hex(random_bytes(5)));
            $code = substr($raw, 0, 5).'-'.substr($raw, 5, 5);
            $plain[] = $code;
            $hashes[] = Hash::make($this->normalizeRecoveryCode($code));
        }

        return compact('plain', 'hashes');
    }

    /** @param list<string> $hashes */
    public function consumeRecoveryCode(string $code, array $hashes): ?array
    {
        $normalized = $this->normalizeRecoveryCode($code);

        foreach ($hashes as $index => $hash) {
            if (Hash::check($normalized, $hash)) {
                unset($hashes[$index]);

                return array_values($hashes);
            }
        }

        return null;
    }

    private function normalizeRecoveryCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }

    private function codeAtStep(string $secret, int $step): string
    {
        $key = $this->base32Decode($secret);
        $counter = pack('N2', ($step >> 32) & 0xFFFFFFFF, $step & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $bits = '';

        foreach (str_split($data) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(preg_replace('/[^A-Z2-7]/', '', $encoded) ?? '');
        $bits = '';

        foreach (str_split($encoded) as $character) {
            $position = strpos(self::ALPHABET, $character);

            if ($position === false) {
                throw new RuntimeException('Invalid Base32 TOTP secret.');
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
