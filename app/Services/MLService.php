<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MLService
{

    private function fixUtterance($utterance)
    {
        $utterance = str_replace("\"", "", $utterance);
        $utterance = str_replace("'", "", $utterance);
        $utterance = str_replace("\r\n", ". ", $utterance);
        $utterance = str_replace("\n", ". ", $utterance);
        $utterance = str_replace("\r", ". ", $utterance);

        return $utterance;
    }

    function getIntent(string $utterance)
    {
        if (config("openai.apiKey") == "") {
            return false;
        }

        $utterance = $this->fixUtterance($utterance);

        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

        $url = "https://api.openai.com/v1/chat/completions";

        $headers = array();
        $headers[] = "Authorization: Bearer ".config("openai.apiKey");
        $headers[] = "Content-Type: application/json";

		$data = array(
			"model"         => config("openai.model"),
            "messages" => [
                [
                    "role"      => "user",
                    "content"   => $utterance
                ]
            ],
			"max_tokens"    => intVal(config("openai.maxTokens")),
			"temperature"   => intVal(config("openai.temperature"))
		);


		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($c, CURLOPT_POST, 1);

        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_URL, $url);


        $resultString = curl_exec($c);
        curl_close($c);

        $json = json_decode($resultString);

	    return $json->choices[0]->message->content;         

    }
}