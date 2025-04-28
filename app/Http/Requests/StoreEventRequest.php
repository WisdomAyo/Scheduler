<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
  
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_datetime' => ['required', 'date', 'after:now'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'event_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
{
    return [
        'name.required' => 'Please provide a name for the event.',
        'start_datetime.required' => 'An event start date and time is required.',
        'start_datetime.after' => 'The event must start in the future.',
        'end_datetime.after' => 'The event end time must be after the start time.',
        'max_participants.min' => 'An event must allow at least one participant.',
        'event_image.image' => 'The uploaded file must be an image.',
        'event_image.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
        'event_image.max' => 'The image may not be larger than 2MB.',
    ];
}
}
