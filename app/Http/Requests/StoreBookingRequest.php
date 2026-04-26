<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'sport_type' => ['nullable', 'string', 'max:100'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'match_type' => ['required', 'in:private,open_match'],
            'session_type' => ['nullable', 'in:standard,open_match,coached_match,group_training,private_training,academy_class'],
            'coach_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'max_players' => ['nullable', 'integer', 'min:1', 'max:32'],
            'skill_level' => ['nullable', 'string', 'max:100'],
            'coach_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'participant_ids' => ['nullable', 'array', 'max:31'],
            'participant_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $participantIds = $this->input('participant_ids', []);
            $ownerId = $this->user()?->id;
            $matchType = (string) $this->input('match_type', 'private');
            $maxPlayers = (int) $this->input('max_players', $matchType === 'open_match' ? 4 : 4);

            if ($ownerId !== null && in_array($ownerId, $participantIds, true)) {
                $validator->errors()->add('participant_ids', 'The authenticated user is added automatically and should not be included in participant_ids.');
            }

            if ($matchType === 'open_match' && count($participantIds) > max($maxPlayers - 1, 0)) {
                $validator->errors()->add('participant_ids', 'The number of invited participants exceeds max_players for this session.');
            }
        });
    }
}
