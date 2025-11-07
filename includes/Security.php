<?php

class Security {
    // Ta klasa jest placeholderem na przyszłe funkcjonalności związane z bezpieczeństwem.

    /**
     * W przyszłości można tu zaimplementować:
     * - Generowanie i walidację tokenów JWT (JSON Web Tokens) do autoryzacji sesji.
     * - Zaawansowane czyszczenie danych wejściowych (sanitization).
     * - Ochronę przed atakami CSRF (Cross-Site Request Forgery).
     * - Mechanizmy ograniczania liczby żądań (rate limiting).
     */

    public static function sanitize_input($data) {
        // Prosta funkcja czyszcząca, która może być rozbudowana
        if (is_array($data)) {
            return array_map([self::class, 'sanitize_input'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)));
    }

    public static function create_jwt($user_id) {
        // TODO: Zaimplementować generowanie JWT
        // Zwraca przykładowy token dla celów deweloperskich
        return base64_encode(json_encode(['user_id' => $user_id, 'exp' => time() + 3600]));
    }

    public static function validate_jwt($jwt) {
        // TODO: Zaimplementować walidację JWT
        // Na razie każdy "token" jest ważny
        $decoded = json_decode(base64_decode($jwt), true);
        if ($decoded && isset($decoded['user_id']) && $decoded['exp'] > time()) {
            return (object) ['data' => ['id' => $decoded['user_id']]];
        }
        return false;
    }
}
