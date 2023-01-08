<?php

namespace App\Service;

class JsonResponseService
{
    public function formatErrorResponse($violations)
    {
        if (gettype($violations) == 'string') {
            return [
                'error' => $violations,
                'messages' => [$violations],
            ];
        }

        $messages = [];

        foreach ($violations as $error) {
            array_push($messages, $error->getMessage());
        }

        return [
            'error' => 'Niepoprawnie wypełniony formularz',
            'messages' => $messages,
        ];
    }

    public function formatSuccessResponse(string $message)
    {
        return [
            'success' => $message,
        ];
    }
}
