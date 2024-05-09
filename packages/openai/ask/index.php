<?php
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

function main(array $args) : array {
  $key = isset($args['OPENAI_API_KEY']) ? $args['OPENAI_API_KEY'] : getenv('OPENAI_API_KEY');
  $host = isset($args['OPENAI_API_HOST']) ? $args['OPENAI_API_HOST'] : getenv('OPENAI_API_HOST');
  $model = "gpt-35-turbo";
  $AI = OpenAI::factory()->withApiKey($key)
            ->withBaseUri($host)
            ->make();
  $input = isset($args['input']) ? $args['input'] : '';
  $output = "Please provide a parameter 'input'";
  if ($input) {
    $params = [
      'model'=>$model,
      'messages'=>[
        ['role'=>'system', 'content'=>'You are a helpful assistant.'],
        ['role'=>'user', 'content'=> $input ],
      ]
    ];
    $result = $AI->chat()->create($params);
    $output = $result->choices[0]->message->content;
  }
  return ['body'=>$output];
}