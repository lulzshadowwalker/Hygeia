<?php

namespace App\Http\Response;

use App\Contracts\ResponseBuilder;
use Illuminate\Http\Response;

class JsonResponseBuilder implements ResponseBuilder
{
    protected $data = [];

    protected $meta = [];

    protected $errors = [];

    protected $statusCode = Response::HTTP_OK;

    public function __construct()
    {
        $this->data = [];
    }

    public function data(array $data): self
    {
        $this->data['data'] = $data;

        return $this;
    }

    public function meta(array $meta): self
    {
        $this->data['meta'] = array_merge($this->data['meta'], $meta);

        return $this;
    }

    public function message(string $message): self
    {
        if (! isset($this->data['meta'])) {
            $this->data['meta'] = [];
        }

        $this->data['meta']['message'] = $message;

        return $this;
    }

    public function error(string $title, string $detail, ?int $code = null, array $meta = [], ?string $pointer = null, ?string $indicator = null): self
    {
        $error = [
            'status' => (string) $code,
            'code' => Response::$statusTexts[$code] ?: (string) $code,
            'title' => $title,
            'detail' => $detail,
        ];

        if ($indicator) {
            $error['indicator'] = $indicator;
        }

        if ($meta) {
            $error['meta'] = $meta;
        }

        if ($pointer) {
            $error['source'] = ['pointer' => $pointer];
        }

        $this->errors = [$error];
        $this->statusCode = $code;

        return $this;
    }

    public function build(int $code = Response::HTTP_OK): \Illuminate\Http\JsonResponse
    {
        $response = $this->errors ? ['errors' => $this->errors] : $this->data;

        return response()->json($response, $code);
    }
}
