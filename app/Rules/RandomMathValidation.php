<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Session;

class RandomMathValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Retrieve the user's answer from the form input
        $userAnswer = (int) $value;

        // Retrieve the correct answer from the session
        $correctAnswer = Session::get('correct_math_answer');

        // Check if the user's answer matches the correct answer
        return $userAnswer === $correctAnswer;
    }

    public function message()
    {
        return 'The math question was not answered correctly.';
    }
}
