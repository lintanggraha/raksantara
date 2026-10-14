<?php

namespace App\Http\Controllers;

use App\UserDashboardPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preference = $request->user()->dashboardPreference;

        return response()->json($this->normalizePreference($preference));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'widget_visibility' => 'nullable|array',
            'widget_visibility.*' => 'boolean',
            'widget_order' => 'nullable|array|max:30',
            'widget_order.*' => 'string|max:80',
            'trend_granularity' => 'nullable|in:monthly,quarterly,yearly',
        ]);

        $preference = UserDashboardPreference::firstOrNew([
            'user_id' => (int) $request->user()->id,
        ]);

        if (array_key_exists('widget_visibility', $validated)) {
            $preference->widget_visibility = $validated['widget_visibility'];
        }

        if (array_key_exists('widget_order', $validated)) {
            $order = collect((array) $validated['widget_order'])
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();
            $preference->widget_order = $order;
        }

        if (array_key_exists('trend_granularity', $validated)) {
            $preference->trend_granularity = (string) $validated['trend_granularity'];
        }

        if (!$preference->exists && empty($preference->trend_granularity)) {
            $preference->trend_granularity = 'quarterly';
        }

        $preference->save();

        return response()->json($this->normalizePreference($preference));
    }

    private function normalizePreference(?UserDashboardPreference $preference): array
    {
        return [
            'widget_visibility' => is_array(optional($preference)->widget_visibility)
                ? $preference->widget_visibility
                : [],
            'widget_order' => is_array(optional($preference)->widget_order)
                ? $preference->widget_order
                : [],
            'trend_granularity' => (string) (optional($preference)->trend_granularity ?: 'quarterly'),
        ];
    }
}
