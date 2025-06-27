<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'new_clock_in' => 'required|date_format:H:i',
            'new_clock_out' => 'required|date_format:H:i|after:new_clock_in',
            'remarks' => 'required|string|max:255',
            'new_breaks' => 'array',
            'new_breaks.*.new_break_start' => 'nullable|date_format:H:i',
            'new_breaks.*.new_break_end' => 'nullable|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'new_clock_in.required' => '出勤時刻を入力してください',
            'new_clock_in.date_format' => '出勤時刻は「時:分」形式で入力してください',
            'new_clock_out.required' => '退勤時刻を入力してください',
            'new_clock_out.date_format' => '出勤時刻は「時:分」形式で入力してください',
            'new_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
            'remarks.max' => '備考は255文字以内で入力してください',
            'new_breaks.*.new_break_start.date_format' => '休憩開始時刻は「時:分」形式で入力してください',
            'new_breaks.*.new_break_end.date_format' => '休憩終了時刻は「時:分」形式で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            if ($clockIn && $clockOut && strtotime($clockIn) >= strtotime($clockOut)) {
                $validator->errors()->add('new_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('new_breaks', []);
            foreach ($breaks as $i => $break) {
                $start = $break['new_break_start'] ?? null;
                $end = $break['new_break_end'] ?? null;

                if (($start && !$end) || (!$start && $end)) {
                    $validator->errors()->add("new_breaks.$i.new_break_start", '休憩開始・終了は両方入力してください');
                }
                if ($start && $end && $clockIn && $clockOut) {
                    if (strtotime($start) < strtotime($clockIn) || strtotime($end) > strtotime($clockOut)) {
                        $validator->errors()->add("new_breaks.$i.new_break_start", '休憩時間が勤務時間外です');
                    }
                    if (strtotime($end) <= strtotime($start)) {
                        $validator->errors()->add("new_breaks.$i.new_break_end", '休憩終了時刻は開始時刻より後にしてください');
                    }
                    // if (strtotime($start) > strtotime($clockOut)) {
                    //     $validator->errors()->add("new_breaks.$i.new_break_start", '休憩時間が不適切な値です');
                    // }
                }
            }
        });
    }
}
