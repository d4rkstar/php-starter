<?php
use OpenAI\Client;

/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

//--web true
//--kind php:default
//--param OPENAI_API_KEY $OPENAI_API_KEY
//--param OPENAI_API_HOST $OPENAI_API_HOST

// Include the autoload
require 'vendor/autoload.php';



const MODEL = "gpt-35-turbo";

$AI = null;

function req(string $msg)
{
  $ROLE = <<<EOF
  When requested to write code, pick Javascript.
  When requested to show chess position, always use the FEN notation.
  When showing HTML, put the code in the body tag. excluding HEAD and HTML tags
EOF;
  return [
    'messages' => [
      ["role" => "system", "content" => $ROLE],
      ["role" => "user", "content" => $msg]
    ]
  ];
}

function ask(string $input): string
{
  global $AI;

  $params = req($input);
  try {
    $result = $AI->chat()->create($params);
    if (count($result->choices) > 0) {
      return $result->choices[0]->message->content;
    }
  } catch (\Exception $ex) {
    print $ex->getTraceAsString();
  }
  return "ERROR";
}

function extractMsg(string $text): array
{
  $res = [];
  // search for a chess position
  $chessPattern = "/(([rnbqkpRNBQKP1-8]{1,8}\/){7}[rnbqkpRNBQKP1-8]{1,8} [bw] (-|K?Q?k?q?) (-|[a-h][36]) \d+ \d+)/";
  $chessMatches = null;
  if (preg_match($chessPattern, $text, $chessMatches, PREG_OFFSET_CAPTURE, 0)) {
    if (count($chessMatches) > 0) {
      $res['chess'] = $chessMatches[0][0];
      return $res;
    }
  }

  $codePattern = '/```(\w+)\n(.*)```/ms';
  $codeMatches = null;
  if (preg_match($codePattern, $text, $codeMatches, PREG_OFFSET_CAPTURE, 0)) {

    if (count($codeMatches) > 0) {
      $language = $codeMatches[1][0];
      $code = $codeMatches[2][0];
      if ($language === "html") {        
        // extract the body if any
        $bodyPattern =  '/<body.*?>(.*?)<\/body>/ms';
        $bodyMatches = [];
        if (preg_match($bodyPattern, $code, $bodyMatches, PREG_OFFSET_CAPTURE, 0)) {          
          if (count($bodyMatches) > 0) {
            $html = $bodyMatches[1][0];
          }
          $res['html'] = $html;
        }        
      } else {
        $res['language'] = $language;
        $res['code'] = $code;
      }     
    }
  }

  return $res;
}

function main(array $args): array
{
  global $AI;

  $key = isset($args['OPENAI_API_KEY']) ? $args['OPENAI_API_KEY'] : getenv('OPENAI_API_KEY');
  $host = isset($args['OPENAI_API_HOST']) ? $args['OPENAI_API_HOST'] : getenv('OPENAI_API_HOST');
  $host = str_replace('https://', '', $host);

  $model = "gpt-35-turbo";
  $AI = OpenAI::factory()
    ->withBaseUri(sprintf('%s/openai/deployments/%s', $host, $model))
    ->withHttpHeader('api-key', $key)
    ->withQueryParam('api-version', '2023-12-01-preview')
    ->make();

  $input = isset($args['input']) ? $args['input'] : '';
  $res = [
    "output" => "Welcome to the OpenAI demo chat",
    "title" => "OpenAI Chat",
    "message" => "You can chat with OpenAI.",
  ];
  if ($input) {
    $output = ask($input);
    $res = extractMsg($output);
    $res['output'] = $output;
  }
  return ['body' => $res];
}