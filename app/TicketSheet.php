<?php

namespace App;

use App\Models\PlatformSetting;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;

class TicketSheet
{
    public const LAYOUTS = ['6' => [2, 3], '8' => [2, 4], '10' => [2, 5], '12' => [3, 4]];

    public const PREFERENCES = [
        'style' => 'classic', 'color' => 'blue', 'layout' => '8', 'numberCards' => false,
        'watermark' => true, 'watermarkOpacity' => '10', 'watermarkSize' => '22',
    ];

    public const PALETTES = [
        'orange' => ['label' => 'Orange', 'accent' => '#b74709', 'tint' => '#fff0e2', 'light' => '#ffb574'],
        'blue' => ['label' => 'Bleu', 'accent' => '#1d4ed8', 'tint' => '#eff6ff', 'light' => '#93c5fd'],
        'green' => ['label' => 'Vert', 'accent' => '#047857', 'tint' => '#ecfdf5', 'light' => '#6ee7b7'],
        'purple' => ['label' => 'Violet', 'accent' => '#6d28d9', 'tint' => '#f5f3ff', 'light' => '#c4b5fd'],
        'rose' => ['label' => 'Rose', 'accent' => '#be185d', 'tint' => '#fdf2f8', 'light' => '#f9a8d4'],
        'graphite' => ['label' => 'Graphite', 'accent' => '#374151', 'tint' => '#f3f4f6', 'light' => '#d1d5db'],
    ];

    /** @return array<string, mixed> */
    public static function defaults(string $businessName, Setting $settings): array
    {
        $platform = PlatformSetting::current();
        $preferences = $platform->ticket_preferences;

        return [
            'businessName' => mb_substr($businessName, 0, 60), 'phone' => $settings->contact_phone,
            'address' => mb_substr($settings->address, 0, 60), 'dailyAmount' => '', 'month' => now()->format('Y-m'),
            'footer' => mb_substr($settings->ticket_footer ?: 'Conservez cette carte et présentez-la à chaque versement.', 0, 120),
            ...$preferences,
            'pageCount' => '1', 'startNumber' => '1',
        ];
    }

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'businessName' => ['required', 'string', 'max:60'], 'phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:60'], 'dailyAmount' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'month' => ['nullable', 'date_format:Y-m'], 'footer' => ['nullable', 'string', 'max:120'],
            'watermark' => ['required', 'boolean'],
            'watermarkOpacity' => ['required', 'integer', 'between:3,20'],
            'watermarkSize' => ['required', 'integer', 'between:15,30'],
            'color' => ['required', Rule::in(array_keys(self::PALETTES))],
            'style' => ['required', Rule::in(['classic', 'orange', 'minimal'])],
            'layout' => ['required', Rule::in(array_keys(self::LAYOUTS))], 'pageCount' => ['required', 'integer', 'between:1,20'],
            'numberCards' => ['required', 'boolean'], 'startNumber' => ['required', 'integer', 'between:1,9999760'],
        ];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'businessName.required' => 'Indiquez le nom du tontinier.', 'businessName.max' => 'Le nom ne doit pas dépasser 60 caractères.',
            'phone.max' => 'Le téléphone ne doit pas dépasser 25 caractères.', 'address.max' => 'L’adresse ne doit pas dépasser 60 caractères.',
            'dailyAmount.integer' => 'La mise doit être un nombre entier de GNF.', 'dailyAmount.min' => 'La mise doit être supérieure à zéro.',
            'dailyAmount.max' => 'La mise dépasse la limite autorisée.', 'month.date_format' => 'Choisissez un mois valide.',
            'footer.max' => 'Le message ne doit pas dépasser 120 caractères.', 'watermarkOpacity.between' => 'La visibilité doit être comprise entre 3 et 20 %.',
            'watermarkSize.between' => 'La taille doit être comprise entre 15 et 30 %.', 'color.in' => 'Choisissez une couleur de la palette.', 'style.in' => 'Choisissez un style de ticket proposé.', 'layout.in' => 'Choisissez 6, 8, 10 ou 12 tickets par feuille.',
            'pageCount.between' => 'Choisissez entre 1 et 20 feuilles.', 'startNumber.between' => 'Le premier numéro doit être compris entre 1 et 9 999 760.',
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function design(array $data): array
    {
        $platform = PlatformSetting::current();
        $data = array_replace($data, array_intersect_key($platform->ticket_preferences, array_flip(['watermark', 'watermarkOpacity', 'watermarkSize'])));
        [$columns, $rows] = self::LAYOUTS[(string) $data['layout']];
        $month = empty($data['month']) ? null : CarbonImmutable::createFromFormat('!Y-m', $data['month']);

        return [
            ...$data, 'palette' => self::PALETTES[$data['color']], 'columns' => $columns, 'rows' => $rows, 'cardsPerPage' => (int) $data['layout'],
            'pages' => (int) $data['pageCount'], 'totalCards' => (int) $data['layout'] * (int) $data['pageCount'],
            'watermark' => (bool) $data['watermark'], 'watermarkOpacity' => (int) $data['watermarkOpacity'], 'watermarkSize' => (int) $data['watermarkSize'],
            'numberCards' => (bool) $data['numberCards'], 'startNumber' => (int) $data['startNumber'],
            'monthLabel' => $month?->locale('fr')->translatedFormat('F Y'), 'monthBadge' => $month?->locale('fr')->translatedFormat('M Y'),
            'daysInMonth' => $month?->daysInMonth ?? 31, 'calendarOffset' => $month ? $month->isoWeekday() - 1 : 0,
            'calendarWeeks' => (int) ceil((($month ? $month->isoWeekday() - 1 : 0) + ($month?->daysInMonth ?? 31)) / 7),
        ];
    }
}
