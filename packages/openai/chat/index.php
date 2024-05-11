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
When showing HTML, always include what is in the body tag,
but exclude the code surrounding the actual content.
So exclude always BODY, HEAD and HTML .
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
  $res = array();

    // search for a chess position
    $chessPattern = '/(([rnbqkpRNBQKP1-8]{1,8}\/){7}[rnbqkpRNBQKP1-8]{1,8} [bw] (-|K?Q?k?q?) (-|[a-h][36]) \d+ \d+)/';
    preg_match_all($chessPattern, $text, $chessMatches);
    if (!empty($chessMatches[0])) {
        $res['chess'] = $chessMatches[0][0];
        return $res;
    }

    // search for code
    $codePattern = '/```(\w+)\n(.*?)```/s';
    preg_match_all($codePattern, $text, $codeMatches);
    if (!empty($codeMatches[0])) {
        $match = $codeMatches;
        if ($match[1][0] === "html") {
            $html = $match[2][0];
            // extract the body if any
            $bodyPattern = '/<body.*?>(.*?)<\/body>/s';
            preg_match($bodyPattern, $html, $bodyMatch);
            if (!empty($bodyMatch[0])) {
                $html = $bodyMatch[0];
            }
            $res['html'] = $html;
            return $res;
        }
        $res['language'] = $match[1][0];
        $res['code'] = $match[2][0];
        return $res;
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
  
  if ($input) {
    $output = ask($input);
    $res = extractMsg($output);
    $res['output'] = $output;
  } else {
    $res = [
      "output" => "Welcome to the OpenAI demo chat",
      "title" => "OpenAI Chat",
      "message" => "You can chat with OpenAI.",
    ];
  }
  return ['body' => $res];
}