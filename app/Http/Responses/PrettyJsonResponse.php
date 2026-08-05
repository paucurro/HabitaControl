<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class PrettyJsonResponse extends JsonResponse
{

  /**
   * @param mixed $data
   * @param int $status
   * @param array $headers
   * @param int $options
   * @param bool $json
   * @return void
   */
  public function __construct($data = null, $status = 200, $headers = [], $options = 0, $json = false)
  {
    $options |= JSON_PRETTY_PRINT;
    parent::__construct($data, $status, $headers, $options, $json);
  }

}